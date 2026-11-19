<?php

if ( PHP_SAPI !== 'cli' ) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/beacon_store.php';

$failures = 0;

function beacon_test($cond, $message) {
    global $failures;
    if ( $cond ) return;
    $failures++;
    fwrite(STDERR, "FAIL: " . $message . "\n");
}

function beacon_test_preimage($host, $owneremail) {
    $preimage = (string) $host;
    if ( is_string($owneremail) && $owneremail !== '' ) {
        $preimage .= "\n" . $owneremail;
    }
    return $preimage . "\n" . "42";
}

function beacon_test_payload($over = array()) {
    $body = array(
        'report_version' => 1,
        'marker' => 'tsugi-upgrade-beacon',
        'reported_at' => '2026-11-18T18:28:00Z',
        'comment' => 'after migrations',
        'host' => 'learn.example.edu',
        'servicename' => 'Example',
        'ownername' => 'Pat',
        'owneremail' => 'pat@example.edu',
        'tsugi_version' => '26.11.5',
        'database_version' => 20261105,
        'php_version' => '8.4.1',
        'mysql_version' => '8.0.36',
        'timezone' => 'Pacific/Honolulu',
        'git_commit' => '3D6F55584',
        'git_branch' => 'upgrade-beacon',
        'scale' => 99,
        'google_login' => true,
    );
    foreach ( $over as $key => $value ) {
        $body[$key] = $value;
    }
    $email = $body['owneremail'] ?? null;
    $emailBytes = (is_string($email) && $email !== '') ? $email : null;
    $host = array_key_exists('host', $body) && $body['host'] !== null ? (string) $body['host'] : '';
    $body['install_id'] = hash('sha256', beacon_test_preimage($host, $emailBytes));
    if ( array_key_exists('install_id', $over) ) $body['install_id'] = $over['install_id'];
    return $body;
}

function beacon_test_open() {
    global $BEACON_TABLE_PREFIX, $BEACON_TEST_PDO;
    $BEACON_TABLE_PREFIX = 'zbeacon' . getmypid() . '_';
    $pdo = beacon_connect();
    $pdo->exec('DROP TABLE IF EXISTS `' . beacon_table('history') . '`');
    $pdo->exec('DROP TABLE IF EXISTS `' . beacon_table('install') . '`');
    $pdo->exec('DROP TABLE IF EXISTS `' . beacon_table('schema') . '`');
    beacon_migrate($pdo);
    $BEACON_TEST_PDO = $pdo;
    return $pdo;
}

function beacon_test_close() {
    global $BEACON_TEST_PDO;
    if ( ! $BEACON_TEST_PDO instanceof PDO ) return;
    $BEACON_TEST_PDO->exec('DROP TABLE IF EXISTS `' . beacon_table('history') . '`');
    $BEACON_TEST_PDO->exec('DROP TABLE IF EXISTS `' . beacon_table('install') . '`');
    $BEACON_TEST_PDO->exec('DROP TABLE IF EXISTS `' . beacon_table('schema') . '`');
    $BEACON_TEST_PDO = null;
}

register_shutdown_function('beacon_test_close');

$withEmail = beacon_test_preimage('learn.example.edu', 'pat@example.edu');
$noEmail = beacon_test_preimage('learn.example.edu', null);
beacon_test(beacon_install_id('learn.example.edu', 'pat@example.edu') === hash('sha256', $withEmail), 'id includes owner email');
beacon_test(beacon_install_id('learn.example.edu', null) === hash('sha256', $noEmail), 'id omits empty owner');
beacon_test(beacon_install_id('learn.example.edu', '') === hash('sha256', $noEmail), 'blank owner is omitted');
beacon_test(beacon_install_id('', null) === hash('sha256', beacon_test_preimage('', null)), 'a missing host is an empty string');
$spaced = ' Pat@Example.edu ';
beacon_test(
    beacon_install_id('learn.example.edu', $spaced) !== beacon_install_id('learn.example.edu', 'pat@example.edu'),
    'owner email is not trimmed or lowercased'
);

foreach ( array('https://localhost/tsugi', 'http://LOCALHOST:8888/x', 'http://127.0.0.1:8888/tsugi', 'http://[::1]/tsugi', 'https://local.example.edu', 'https://dev.local/a', 'https://box.localhost/a') as $localUrl ) {
    beacon_test(beacon_wwwroot_is_local($localUrl), 'local ' . $localUrl);
}
foreach ( array('https://www.tsugi.org/', 'https://localhost.example.com/', 'https://example.com/local') as $publicUrl ) {
    beacon_test( ! beacon_wwwroot_is_local($publicUrl), 'public ' . $publicUrl);
}

$pdo = beacon_test_open();
$tInstall = beacon_table('install');
$tHistory = beacon_table('history');
$tSchema = beacon_table('schema');
$raw = json_encode(beacon_test_payload());
$accepted = beacon_accept($raw);
beacon_test($accepted['ok'] === true, 'accepts a report');
beacon_test( ! array_key_exists('scale', $accepted['snapshot']), 'unknown field is not stored');
beacon_test($accepted['snapshot']['git_commit'] === '3d6f55584', 'commit is stored lowercase');
beacon_test((int) $accepted['snapshot']['software_version'] === 20261105, 'database version is stored');
beacon_test($accepted['snapshot']['php'] === '8.4.1', 'php version is stored');
beacon_test($accepted['snapshot']['timezone'] === 'Pacific/Honolulu', 'timezone is stored');
beacon_test($accepted['snapshot']['server_version'] === '8.0.36', 'mysql version is stored');
beacon_test($accepted['snapshot']['wwwroot'] === 'learn.example.edu', 'host is stored');
beacon_test($accepted['snapshot']['local'] === 0, 'public host is not local');
beacon_test($accepted['snapshot']['owneremail'] === 'pat@example.edu', 'email stored as posted');

list($status, $body) = beacon_handle('POST', $raw, $pdo);
beacon_test($status === 200 && $body === array('ok' => true), 'stores and returns 2xx');
beacon_test(strpos(json_encode($body), $withEmail) === false, 'response does not contain the preimage');
beacon_test(strpos(json_encode($body), '42') === false, 'response does not contain the preimage line');

$changed = beacon_test_payload(array(
    'tsugi_version' => '26.12.0',
    'database_version' => 20261200,
    'reported_at' => '2026-12-01T00:00:00Z',
    'comment' => 'second note',
));
list($status2, $body2) = beacon_handle('POST', json_encode($changed), $pdo);
beacon_test($status2 === 200, 'second report updates the same install');
$rows = beacon_list($pdo, false);
beacon_test(count($rows) === 1, 'upsert keeps a single snapshot');
beacon_test($rows[0]['tsugi_version'] === '26.12.0', 'snapshot is the latest report');
beacon_test(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $rows[0]['created_at']) === 1, 'created_at is UTC');
beacon_test(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $rows[0]['updated_at']) === 1, 'updated_at is UTC');
beacon_test((int) $rows[0]['upgrade_count'] === 2, 'each report appends history');
$history = beacon_history_for($pdo, $rows[0]['id']);
beacon_test(count($history) === 2, 'two history rows');
beacon_test((int) $history[0]['software_version'] === 20261200, 'newest history is first');
beacon_test((int) $history[0]['http_status'] === 200, 'history records the status we returned');
beacon_test($history[0]['git_branch'] === 'upgrade-beacon', 'history keeps the branch');
beacon_test($history[0]['comment'] === 'second note' && $history[1]['comment'] === 'after migrations', 'history keeps each report comment');
beacon_test($history[0]['php'] === '8.4.1' && $history[1]['php'] === '8.4.1', 'history keeps the php version');
$countedId = $rows[0]['id'];
$oldHistoryId = $pdo->prepare('SELECT id FROM `' . $tHistory . '` WHERE install_id = ? ORDER BY id ASC LIMIT 1');
$oldHistoryId->execute(array($countedId));
$pdo->prepare('DELETE FROM `' . $tHistory . '` WHERE id = ?')->execute(array($oldHistoryId->fetchColumn()));
$keptCount = beacon_one($pdo, $countedId);
beacon_test((int) $keptCount['upgrade_count'] === 2, 'upgrade count stays after a history row is removed');
beacon_test($history[0]['server_version'] === '8.0.36' && $history[1]['server_version'] === '8.0.36', 'history keeps the mysql version');

$badId = beacon_test_payload();
$badId['install_id'] = str_repeat('a', 64);
list($badStatus, $badBody) = beacon_handle('POST', json_encode($badId), $pdo);
beacon_test($badStatus === 400 && $badBody === array('error' => 'bad request'), 'mismatched id is 400');
beacon_test(count(beacon_list($pdo, true)) === 1, 'rejected id writes nothing');

$localBody = beacon_test_payload(array(
    'host' => 'localhost',
    'owneremail' => null,
));
list($localStatus,) = beacon_handle('POST', json_encode($localBody), $pdo);
beacon_test($localStatus === 200, 'local report is stored');
beacon_test(count(beacon_list($pdo, false)) === 1, 'default list hides local');
$withLocal = beacon_list($pdo, true);
beacon_test(count($withLocal) === 2, 'local row is still stored');

$moved = beacon_test_payload(array(
    'host' => 'learn.example.edu',
    'owneremail' => 'other@example.edu',
));
list($movedStatus,) = beacon_handle('POST', json_encode($moved), $pdo);
beacon_test($movedStatus === 200, 'a different owner email is a new install');
$publicRows = beacon_list($pdo, false);
beacon_test(count($publicRows) === 2, 'same host has two installs');
$seen = array();
foreach ( $publicRows as $row ) $seen[$row['wwwroot']] = true;
beacon_test(count($seen) === 1, 'those installs group by host');

$version = $pdo->query('SELECT version FROM `' . $tSchema . '`')->fetchColumn();
beacon_test((int) $version === 2, 'migration records schema version 2');
beacon_open();
$versionRows = $pdo->query('SELECT COUNT(*) FROM `' . $tSchema . '`')->fetchColumn();
beacon_test((int) $versionRows === 1, 'migration does not insert a second version row');
$pdo->exec('ALTER TABLE `' . $tHistory . '` DROP COLUMN comment');
beacon_schema_set($pdo, 1);
beacon_migrate($pdo);
$migratedHistory = beacon_history_for($pdo, $rows[0]['id']);
beacon_test($migratedHistory[0]['comment'] === '', 'migration 2 adds comment onto history rows already stored');

$cases = array(
    array('report_version' => 2),
    array('report_version' => '1'),
    array('marker' => 'nope'),
    array('reported_at' => '2026-11-18 18:28:00'),
    array('git_commit' => 'not-hex'),
    array('database_version' => '20261105'),
    array('host' => null, 'install_id' => str_repeat('b', 64)),
);
foreach ( $cases as $case ) {
    $payload = beacon_test_payload($case);
    $result = beacon_accept(json_encode($payload));
    beacon_test($result['ok'] === false, 'rejects ' . json_encode($case));
}

$garbage = array(
    '{',
    'null',
    '[]',
    '',
);
foreach ( $garbage as $item ) {
    list($gStatus, $gBody) = beacon_handle('POST', $item, $pdo);
    beacon_test($gStatus === 400 && $gBody === array('error' => 'bad request'), 'bad json stays a 400');
    beacon_test(strpos(json_encode($gBody), '42') === false, 'error body is generic');
}
list($getStatus,) = beacon_handle('GET', $raw, $pdo);
beacon_test($getStatus === 405, 'GET is not a stored report');
putenv('TSUGI_BEACON_REFUSE_CONNECT=1');
$refusedBad = false;
$refusedStatus = 0;
try {
    list($refusedStatus,) = beacon_handle('POST', '{');
} catch ( Throwable $e ) {
    $refusedBad = true;
}
$refusedComment = false;
$commentStatus = 0;
try {
    list($commentStatus,) = beacon_handle('POST', json_encode(beacon_test_payload(array(
        'comment' => str_repeat('a', BEACON_MAX_COMMENT + 1),
    ))));
} catch ( Throwable $e ) {
    $refusedComment = true;
}
$refusedGood = false;
try {
    beacon_handle('POST', json_encode(beacon_test_payload(array(
        'host' => 'refuse.example.edu',
        'owneremail' => 'refuse@example.edu',
    ))));
} catch ( Throwable $e ) {
    $refusedGood = true;
}
putenv('TSUGI_BEACON_REFUSE_CONNECT');
beacon_test( ! $refusedBad && $refusedStatus === 400, 'a report that cannot be stored does not open the database');
beacon_test( ! $refusedComment && $commentStatus === 400, 'a comment over 1K does not open the database');
beacon_test($refusedGood, 'a storeable report is what opens the database');
$beforeBig = (int) $pdo->query('SELECT COUNT(*) FROM `' . $tInstall . '`')->fetchColumn();
$oversized = str_repeat('x', BEACON_MAX_BODY + 1);
list($bigStatus, $bigBody) = beacon_handle('POST', $oversized, $pdo);
beacon_test($bigStatus === 400 && $bigBody === array('error' => 'bad request'), 'a body over 5KB is rejected');
beacon_test(beacon_accept(str_repeat('x', BEACON_MAX_BODY))['ok'] === false, '5KB of junk is not stored');
beacon_test($beforeBig === (int) $pdo->query('SELECT COUNT(*) FROM `' . $tInstall . '`')->fetchColumn(), 'rejected body does not add a row');

$cli = beacon_test_payload();
$cli['mysql_version'] = null;
$cli['git_commit'] = null;
$cli['git_branch'] = null;
unset($cli['comment']);
$cliAccepted = beacon_accept(json_encode($cli));
beacon_test($cliAccepted['ok'] === true, 'a missing comment and null git are accepted');
beacon_test($cliAccepted['snapshot']['server_version'] === null, 'null mysql version stays null');
beacon_test($cliAccepted['snapshot']['git_commit'] === null && $cliAccepted['snapshot']['git_branch'] === null, 'detached git is null');
beacon_test($cliAccepted['snapshot']['comment'] === '', 'missing comment stores empty');
beacon_test($cliAccepted['history']['comment'] === '', 'history keeps a missing comment empty');
$commentOk = beacon_test_payload(array('comment' => str_repeat('a', BEACON_MAX_COMMENT)));
beacon_test(beacon_accept(json_encode($commentOk))['ok'] === true, 'a 1K comment can be stored');
$commentBig = beacon_test_payload(array('comment' => str_repeat('a', BEACON_MAX_COMMENT + 1)));
beacon_test(beacon_accept(json_encode($commentBig))['ok'] === false, 'a comment over 1K cannot be stored');

$spacedBody = beacon_test_payload(array(
    'host' => 'spaced.example.edu',
    'owneremail' => ' Pat@Example.edu ',
));
$spacedAccepted = beacon_accept(json_encode($spacedBody));
beacon_test($spacedAccepted['ok'] === true, 'spaced email matches the untrimmed preimage');
beacon_test($spacedAccepted['snapshot']['owneremail'] === ' Pat@Example.edu ', 'stored email keeps spaces and case');

$stampAccepted = beacon_accept(json_encode(beacon_test_payload(array(
    'host' => 'stamps.example.edu',
    'owneremail' => 'a@example.edu',
))));
$otherAccepted = beacon_accept(json_encode(beacon_test_payload(array(
    'host' => 'stamps.example.edu',
    'owneremail' => 'b@example.edu',
))));
$tieAccepted = beacon_accept(json_encode(beacon_test_payload(array(
    'host' => 'stamps.example.edu',
    'owneremail' => 'c@example.edu',
))));
beacon_save($pdo, $stampAccepted['snapshot'], $stampAccepted['history'], 200, '2026-01-01T00:00:00Z');
beacon_save($pdo, $otherAccepted['snapshot'], $otherAccepted['history'], 200, '2026-06-01T00:00:00Z');
beacon_save($pdo, $stampAccepted['snapshot'], $stampAccepted['history'], 200, '2026-08-01T00:00:00Z');
beacon_save($pdo, $tieAccepted['snapshot'], $tieAccepted['history'], 200, '2026-08-01T00:00:00Z');
$stampRow = beacon_one($pdo, $stampAccepted['snapshot']['id']);
beacon_test($stampRow['created_at'] === '2026-01-01T00:00:00Z', 'created_at stays on the first write');
beacon_test($stampRow['updated_at'] === '2026-08-01T00:00:00Z', 'updated_at moves on the next write');
$stampHistory = beacon_history_for($pdo, $stampAccepted['snapshot']['id']);
beacon_test($stampHistory[0]['received_at'] === '2026-08-01T00:00:00Z', 'history sorts by when the report was received');
beacon_test($stampHistory[1]['received_at'] === '2026-01-01T00:00:00Z', 'an older history row keeps its received time');
$stampList = array();
foreach ( beacon_list($pdo, false) as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $stampList[] = $stampItem['owneremail'];
}
beacon_test($stampList === array('c@example.edu', 'a@example.edu', 'b@example.edu'), 'list sorts by updated_at then created_at');
$createdList = array();
foreach ( beacon_list($pdo, false, 'created_at') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $createdList[] = $stampItem['owneremail'];
}
beacon_test($createdList === array('c@example.edu', 'b@example.edu', 'a@example.edu'), 'list can sort by created_at then updated_at');
$createdAsc = array();
foreach ( beacon_list($pdo, false, 'created_at', 'asc') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $createdAsc[] = $stampItem['owneremail'];
}
beacon_test($createdAsc === array('a@example.edu', 'b@example.edu', 'c@example.edu'), 'created_at can sort ascending');
$foundDirs = array();
foreach ( beacon_list($pdo, false, 'updated_at', 'desc', 'b@example.edu') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $foundDirs[] = $stampItem['owneremail'];
}
beacon_test($foundDirs === array('b@example.edu'), 'search matches an owner email');
$caseHits = 0;
foreach ( beacon_list($pdo, false, 'wwwroot', 'asc', 'StAmPs.ExAmPle') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $caseHits++;
}
beacon_test($caseHits === 3, 'search is case insensitive');
$phpHits = 0;
foreach ( beacon_list($pdo, false, 'php', 'asc', '8.4.1') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $phpHits++;
}
beacon_test($phpHits === 3, 'search matches a php version');
beacon_test(count(beacon_list($pdo, false, 'updated_at', 'desc', 'no-such-beacon-xyz')) === 0, 'a miss returns no rows');
$fallback = array();
foreach ( beacon_list($pdo, false, 'not-a-column', 'sideways') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) $fallback[] = $stampItem['owneremail'];
}
beacon_test($fallback === $stampList, 'an unknown sort falls back to updated_at descending');
$byInstalls = array();
foreach ( beacon_list($pdo, false, 'install_count', 'desc') as $stampItem ) {
    $byInstalls[] = $stampItem['wwwroot'];
    if ( $stampItem['wwwroot'] === 'stamps.example.edu' ) {
        beacon_test((int) $stampItem['install_count'] === 3, 'install count is how many rows share the host');
    }
}
beacon_test($byInstalls[0] === 'stamps.example.edu' && $byInstalls[count($byInstalls) - 1] === 'learn.example.edu', 'list sorts by number of installs');
$searchedCount = beacon_list($pdo, false, 'install_count', 'desc', 'b@example.edu');
beacon_test(count($searchedCount) === 1 && (int) $searchedCount[0]['install_count'] === 1, 'install count follows the rows in the list');

$pdo->exec('DELETE FROM `' . $tHistory . '`');
$pdo->exec('DELETE FROM `' . $tInstall . '`');
$insertOld = $pdo->prepare('INSERT INTO `' . $tInstall . '` (
        id, wwwroot, local, reported_at, comment, created_at, updated_at, upgrade_count
    ) VALUES (?, ?, 0, ?, \'\', ?, ?, 0)');
$insertHist = $pdo->prepare('INSERT INTO `' . $tHistory . '` (
        install_id, reported_at, software_version, http_status, received_at
    ) VALUES (?, ?, 1, 200, ?)');
for ( $n = 1; $n <= 4; $n++ ) {
    $oldId = str_repeat((string) $n, 64);
    $stamp = '2020-0' . $n . '-01T00:00:00Z';
    $insertOld->execute(array($oldId, 'https://old-' . $n . '.example.edu', $stamp, $stamp, $stamp));
    $insertHist->execute(array($oldId, $stamp, $stamp));
}
beacon_prune($pdo, 3);
$kept = $pdo->query('SELECT wwwroot FROM `' . $tInstall . '` ORDER BY updated_at ASC')->fetchAll(PDO::FETCH_COLUMN);
beacon_test($kept === array(
    'https://old-2.example.edu',
    'https://old-3.example.edu',
    'https://old-4.example.edu',
), 'prune keeps the 3 most recent installs');
$goneHistory = (int) $pdo->query('SELECT COUNT(*) FROM `' . $tHistory . '` WHERE install_id = \'' . str_repeat('1', 64) . '\'')->fetchColumn();
beacon_test($goneHistory === 0, 'prune deletes history for the earliest install');
beacon_test(BEACON_MAX_HISTORY === 20000, 'history is capped at 20000 rows');
for ( $n = 1; $n <= 3; $n++ ) {
    $stamp = '2019-0' . $n . '-01T00:00:00Z';
    $insertHist->execute(array(str_repeat('2', 64), $stamp, $stamp));
}
beacon_prune_history($pdo, 4);
$historyLeft = $pdo->query('SELECT reported_at FROM `' . $tHistory . '` ORDER BY received_at ASC, id ASC')->fetchAll(PDO::FETCH_COLUMN);
beacon_test($historyLeft === array(
    '2019-03-01T00:00:00Z',
    '2020-02-01T00:00:00Z',
    '2020-03-01T00:00:00Z',
    '2020-04-01T00:00:00Z',
), 'history prune keeps the most recent rows');
beacon_test((int) $pdo->query('SELECT COUNT(*) FROM `' . $tInstall . '`')->fetchColumn() === 3, 'history prune leaves the installs');
$deleteId = str_repeat('d', 64);
$pdo->prepare('INSERT INTO `' . $tInstall . '` (
        id, wwwroot, local, reported_at, comment, created_at, updated_at, upgrade_count
    ) VALUES (?, \'https://delete.example.edu\', 0, \'2020-01-01T00:00:00Z\', \'\', \'2020-01-01T00:00:00Z\', \'2020-01-01T00:00:00Z\', 0)')->execute(array($deleteId));
$pdo->prepare('INSERT INTO `' . $tHistory . '` (
        install_id, reported_at, software_version, http_status, received_at
    ) VALUES (?, \'2020-01-01T00:00:00Z\', 1, 200, \'2020-01-01T00:00:00Z\')')->execute(array($deleteId));
beacon_test(beacon_delete($pdo, 'not-an-id') === false, 'delete rejects a bad id');
beacon_test(beacon_delete($pdo, $deleteId) === true, 'delete removes an install');
beacon_test(beacon_one($pdo, $deleteId) === null, 'deleted install is gone');
beacon_test(count(beacon_history_for($pdo, $deleteId)) === 0, 'delete removes that install history');
beacon_test((int) $pdo->query('SELECT COUNT(*) FROM `' . $tInstall . '`')->fetchColumn() === 3, 'prune stops at the cap');

unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_CF_CONNECTING_IP']);
beacon_test(beacon_client_ip() === null, 'a missing address stays empty');
$_SERVER['REMOTE_ADDR'] = '198.51.100.20';
$_SERVER['HTTP_CF_CONNECTING_IP'] = 'not an ip';
beacon_test(beacon_client_ip() === '198.51.100.20', 'an invalid cloudflare header falls back to the socket address');
$_SERVER['HTTP_CF_CONNECTING_IP'] = ' 203.0.113.9 ';
beacon_test(beacon_client_ip() === '203.0.113.9', 'the cloudflare connecting address is the client');
beacon_test(beacon_clean_host('203.0.113.9', '203.0.113.9') === null, 'a reverse lookup that echoes the address is empty');
beacon_test(beacon_clean_host('203.0.113.9', 'learn.example.edu.') === 'learn.example.edu', 'a reverse name drops the trailing dot');
beacon_test(beacon_clean_host('203.0.113.9', "bad\nhost") === null, 'a reverse name with a control character is dropped');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
unset($_SERVER['HTTP_CF_CONNECTING_IP']);
$ipBody = beacon_test_payload(array(
    'host' => 'ip.example.edu',
    'owneremail' => null,
));
list($ipStatus,) = beacon_handle('POST', json_encode($ipBody), $pdo);
$ipRow = beacon_one($pdo, $ipBody['install_id']);
beacon_test($ipStatus === 200 && $ipRow['client_ip'] === '127.0.0.1', 'a report records the incoming address');
$ipHistory = beacon_history_for($pdo, $ipBody['install_id']);
beacon_test(count($ipHistory) === 1 && $ipHistory[0]['client_ip'] === '127.0.0.1', 'history records the incoming address');
beacon_test($ipRow['client_host'] === null && beacon_reverse_host('127.0.0.1') === null, 'localhost gets no reverse lookup');
$foundIp = beacon_list($pdo, false, 'client_ip', 'asc', '127.0.0.1');
beacon_test(count($foundIp) === 1 && $foundIp[0]['wwwroot'] === 'ip.example.edu', 'search finds the recorded address');
unset($_SERVER['REMOTE_ADDR']);

$placeInstall = $pdo->prepare('INSERT INTO `' . $tInstall . '` (
    id, wwwroot, local, reported_at, comment, created_at, updated_at, client_ip, upgrade_count
) VALUES (?, ?, 0, \'2026-01-01T00:00:00Z\', \'\', \'2026-01-01T00:00:00Z\', \'2026-01-01T00:00:00Z\', ?, 0)');
$placeInstall->execute(array(hash('sha256', 'place-8.8.8.8'), 'place-8.example.edu', '8.8.8.8'));
$placeInstall->execute(array(hash('sha256', 'place-1.1.1.1'), 'place-1.example.edu', '1.1.1.1'));

$placeCalls = 0;
$placeFetch = function ($ip) use (&$placeCalls) {
    $placeCalls++;
    return array('success' => true, 'city' => 'Ann Arbor', 'country_code' => 'us');
};
$placeNow = '2026-11-18T00:00:00Z';
$place = beacon_ip_place($pdo, '8.8.8.8', $placeNow, $placeFetch);
beacon_test($place['city'] === 'Ann Arbor' && $place['country_code'] === 'US', 'a detail lookup keeps the city and country');
beacon_ip_place($pdo, '8.8.8.8', '2026-12-01T00:00:00Z', $placeFetch);
beacon_test($placeCalls === 1, 'a lookup from the past few weeks is reused');
beacon_ip_place($pdo, '8.8.8.8', '2026-12-20T00:00:00Z', $placeFetch);
beacon_test($placeCalls === 2, 'a lookup older than three weeks is refreshed');
$skipped = array('127.0.0.1', '::1', '10.1.2.3', '192.168.1.9', '169.254.1.1', '100.64.1.1', '224.0.0.1', '203.0.113.9', 'localhost');
$skippedOk = true;
foreach ( $skipped as $skip ) {
    if ( beacon_ip_place($pdo, $skip, $placeNow, $placeFetch) !== null ) $skippedOk = false;
}
beacon_test($skippedOk && $placeCalls === 2 && beacon_reverse_host('10.9.8.7') === null, 'local and non-routable addresses are not looked up');
$misses = 0;
$missFetch = function ($ip) use (&$misses) {
    $misses++;
    return array('success' => false);
};
$missed = beacon_ip_place($pdo, '1.1.1.1', $placeNow, $missFetch);
beacon_ip_place($pdo, '1.1.1.1', $placeNow, $missFetch);
beacon_test($missed['city'] === null && $missed['country_code'] === null && $misses === 1, 'a failed lookup is cached');
$blips = 0;
$blipFetch = function ($ip) use (&$blips) {
    $blips++;
    return null;
};
beacon_test(beacon_ip_place($pdo, '9.9.9.9', $placeNow, $blipFetch) === null, 'a location timeout is left empty');
beacon_ip_place($pdo, '9.9.9.9', $placeNow, $blipFetch);
beacon_test($blips === 2, 'a location timeout is tried again later');
$capped = 0;
$capFetch = function ($ip) use (&$capped) {
    $capped++;
    return array('success' => true, 'city' => 'City', 'country_code' => 'US');
};
$many = array();
for ( $i = 1; $i <= 6; $i++ ) $many[] = '8.8.4.' . $i;
$got = beacon_ip_places($pdo, $many, $placeNow, $capFetch);
beacon_test($capped === 4 && count($got) === 4, 'one detail page looks up at most four new addresses');

$stored = json_encode($pdo->query('SELECT * FROM `' . $tInstall . '`')->fetchAll());
beacon_test(strpos($stored, $withEmail) === false, 'stored rows do not contain the preimage');
beacon_test(strpos($stored, "pat@example.edu\n42") === false, 'stored rows do not contain the preimage line');
beacon_test_close();
unset($pdo);

if ( $failures > 0 ) {
    fwrite(STDERR, $failures . " failed\n");
    exit(1);
}
echo "ok\n";
exit(0);
