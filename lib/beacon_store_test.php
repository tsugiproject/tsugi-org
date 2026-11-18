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

function beacon_test_preimage($wwwroot, $dirroot, $owneremail) {
    if ( is_string($owneremail) && $owneremail !== '' ) {
        return $wwwroot . "\n" . $dirroot . "\n" . $owneremail . "\n" . "42";
    }
    return $wwwroot . "\n" . $dirroot . "\n" . "42";
}

function beacon_test_payload($over = array()) {
    $install = array(
        'wwwroot' => 'https://learn.example.edu/tsugi',
        'apphome' => 'https://learn.example.edu',
        'dirroot' => '/var/www/tsugi',
        'servicename' => 'Example',
        'servicedesc' => null,
        'ownername' => 'Pat',
        'owneremail' => 'pat@example.edu',
        'developer' => false,
        'local' => false,
        'lessons' => true,
    );
    if ( isset($over['install']) ) $install = array_merge($install, $over['install']);
    $dirroot = array_key_exists('dirroot', $install) ? $install['dirroot'] : '';
    $dirrootBytes = $dirroot === null ? '' : (string) $dirroot;
    $email = $install['owneremail'] ?? null;
    $emailBytes = (is_string($email) && $email !== '') ? $email : null;
    $install['id'] = hash('sha256', beacon_test_preimage($install['wwwroot'], $dirrootBytes, $emailBytes));
    $body = array(
        'report_version' => 1,
        'marker' => 'tsugi-upgrade-beacon',
        'event' => 'database_upgrade',
        'source' => 'admin/upgrade.php',
        'install' => $install,
        'software' => array(
            'tsugi_version' => '26.11.5',
            'minimum_php' => '8.4',
            'minimum_mysql' => '8.0.16',
        ),
        'database' => array(
            'software_version' => 20261105,
            'highest_migration' => 20261101,
            'highest_migration_path' => 'lib/src/Services/Lti/database.php',
            'recorded_overall' => 20261101,
            'server_version' => '8.0.36',
            'server_below_minimum' => false,
            'driver' => 'mysql',
            'prefix' => 'lti_',
            'schemas' => array(
                'lib/src/Services/Lti/database.php' => 20261101,
                'mod/quiz/database.php' => 12,
            ),
        ),
        'git' => array(
            'commit' => '3D6F55584',
            'branch' => 'upgrade-beacon',
        ),
        'runtime' => array(
            'php' => '8.4.1',
            'sapi' => 'apache2handler',
            'os' => 'Linux',
            'os_release' => '6.1.0',
            'hostname' => 'web1',
            'timezone' => 'UTC',
            'server_software' => 'Apache',
        ),
        'reported_at' => '2026-11-18T18:28:00Z',
        'comment' => 'after migrations',
        'scale' => 99,
        'google_login' => true,
    );
    foreach ( $over as $key => $value ) {
        if ( $key === 'install' ) continue;
        $body[$key] = $value;
    }
    return $body;
}

function beacon_test_open() {
    $path = tempnam(sys_get_temp_dir(), 'beacon');
    unlink($path);
    return array(beacon_open($path), $path);
}

$withEmail = beacon_test_preimage('https://learn.example.edu/tsugi', '/var/www/tsugi', 'pat@example.edu');
$noEmail = beacon_test_preimage('https://learn.example.edu/tsugi', '/var/www/tsugi', null);
beacon_test(beacon_install_id('https://learn.example.edu/tsugi', '/var/www/tsugi', 'pat@example.edu') === hash('sha256', $withEmail), 'id includes owner email');
beacon_test(beacon_install_id('https://learn.example.edu/tsugi', '/var/www/tsugi', null) === hash('sha256', $noEmail), 'id omits empty owner');
beacon_test(beacon_install_id('https://learn.example.edu/tsugi', '/var/www/tsugi', '') === hash('sha256', $noEmail), 'blank owner is omitted');
beacon_test(beacon_install_id('https://learn.example.edu/tsugi', '', null) === hash('sha256', beacon_test_preimage('https://learn.example.edu/tsugi', '', null)), 'null dirroot is empty in the preimage');
$spaced = ' Pat@Example.edu ';
beacon_test(
    beacon_install_id('https://learn.example.edu', '/var', $spaced) !== beacon_install_id('https://learn.example.edu', '/var', 'pat@example.edu'),
    'owner email is not trimmed or lowercased'
);

foreach ( array('https://localhost/tsugi', 'http://LOCALHOST:8888/x', 'http://127.0.0.1:8888/tsugi', 'http://[::1]/tsugi', 'https://local.example.edu', 'https://dev.local/a', 'https://box.localhost/a') as $localUrl ) {
    beacon_test(beacon_wwwroot_is_local($localUrl), 'local ' . $localUrl);
}
foreach ( array('https://www.tsugi.org/', 'https://localhost.example.com/', 'https://example.com/local') as $publicUrl ) {
    beacon_test( ! beacon_wwwroot_is_local($publicUrl), 'public ' . $publicUrl);
}

list($pdo, $path) = beacon_test_open();
$raw = json_encode(beacon_test_payload());
$accepted = beacon_accept($raw);
beacon_test($accepted['ok'] === true, 'accepts a report');
beacon_test( ! array_key_exists('scale', $accepted['snapshot']), 'unknown field is not stored');
beacon_test($accepted['snapshot']['git_commit'] === '3d6f55584', 'commit is stored lowercase');
beacon_test($accepted['snapshot']['recorded_overall'] === 20261101, 'recorded_overall may lag software_version');
beacon_test($accepted['snapshot']['local'] === 0, 'public host is not local');
beacon_test($accepted['snapshot']['owneremail'] === 'pat@example.edu', 'email stored as posted');

list($status, $body) = beacon_handle('POST', $raw, $pdo);
beacon_test($status === 200 && $body === array('ok' => true), 'stores and returns 2xx');
beacon_test(strpos(json_encode($body), $withEmail) === false, 'response does not contain the preimage');
beacon_test(strpos(json_encode($body), '42') === false, 'response does not contain the preimage line');

$changed = beacon_test_payload(array(
    'software' => array('tsugi_version' => '26.12.0', 'minimum_php' => '8.4', 'minimum_mysql' => '8.0.16'),
    'database' => array(
        'software_version' => 20261200,
        'highest_migration' => 20261101,
        'highest_migration_path' => 'lib/src/Services/Lti/database.php',
        'recorded_overall' => 20261101,
        'server_version' => '8.0.36',
        'server_below_minimum' => false,
        'driver' => 'mysql',
        'prefix' => 'lti_',
        'schemas' => array('lib/src/Services/Lti/database.php' => 20261101),
    ),
    'reported_at' => '2026-12-01T00:00:00Z',
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

$badId = beacon_test_payload();
$badId['install']['id'] = str_repeat('a', 64);
list($badStatus, $badBody) = beacon_handle('POST', json_encode($badId), $pdo);
beacon_test($badStatus === 400 && $badBody === array('error' => 'bad request'), 'mismatched id is 400');
beacon_test(count(beacon_list($pdo, true)) === 1, 'rejected id writes nothing');

$localBody = beacon_test_payload(array('install' => array(
    'wwwroot' => 'http://localhost:8888/tsugi-org/tsugi',
    'dirroot' => '/Users/csev/htdocs/tsugi-org/tsugi',
    'owneremail' => null,
    'local' => false,
)));
list($localStatus,) = beacon_handle('POST', json_encode($localBody), $pdo);
beacon_test($localStatus === 200, 'local report is stored');
beacon_test(count(beacon_list($pdo, false)) === 1, 'default list hides local');
$withLocal = beacon_list($pdo, true);
beacon_test(count($withLocal) === 2, 'local row is still stored');

$moved = beacon_test_payload(array('install' => array(
    'wwwroot' => 'https://learn.example.edu/tsugi',
    'dirroot' => '/var/www/releases/26.12.0',
)));
list($movedStatus,) = beacon_handle('POST', json_encode($moved), $pdo);
beacon_test($movedStatus === 200, 'a new dirroot is a new install');
$publicRows = beacon_list($pdo, false);
beacon_test(count($publicRows) === 2, 'same wwwroot has two installs');
$seen = array();
foreach ( $publicRows as $row ) $seen[$row['wwwroot']] = true;
beacon_test(count($seen) === 1, 'those installs group by wwwroot');

$version = $pdo->query('SELECT version FROM beacon_schema')->fetchColumn();
beacon_test((int) $version === 2, 'migration records schema version 2');
beacon_open($path);
$versionRows = $pdo->query('SELECT COUNT(*) FROM beacon_schema')->fetchColumn();
beacon_test((int) $versionRows === 1, 'migration does not insert a second version row');

$cases = array(
    array('report_version' => 2),
    array('report_version' => '1'),
    array('marker' => 'nope'),
    array('event' => 'launch'),
    array('source' => 'cron'),
    array('reported_at' => '2026-11-18 18:28:00'),
    array('git' => array('commit' => 'not-hex', 'branch' => 'main')),
    array('database' => array('schemas' => array('only'))),
);
foreach ( $cases as $case ) {
    $payload = beacon_test_payload($case);
    if ( isset($case['database']) ) {
        $payload['database'] = $case['database'];
    }
    $result = beacon_accept(json_encode($payload));
    beacon_test($result['ok'] === false, 'rejects ' . json_encode($case));
}

$tooMany = array();
for ( $i = 0; $i < 301; $i++ ) $tooMany['mod/p' . $i . '/database.php'] = $i;
$many = beacon_test_payload();
$many['database']['schemas'] = $tooMany;
beacon_test(beacon_accept(json_encode($many))['ok'] === false, 'rejects more than 300 schemas');

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
$beforeBig = (int) $pdo->query('SELECT COUNT(*) FROM beacon_install')->fetchColumn();
$oversized = str_repeat('x', BEACON_MAX_BODY + 1);
list($bigStatus, $bigBody) = beacon_handle('POST', $oversized, $pdo);
beacon_test($bigStatus === 400 && $bigBody === array('error' => 'bad request'), 'a body over 5KB is rejected');
beacon_test(beacon_accept(str_repeat('x', BEACON_MAX_BODY))['ok'] === false, '5KB of junk is not stored');
beacon_test($beforeBig === (int) $pdo->query('SELECT COUNT(*) FROM beacon_install')->fetchColumn(), 'rejected body does not add a row');

$cli = beacon_test_payload();
$cli['runtime']['server_software'] = null;
$cli['git'] = array('commit' => null, 'branch' => null);
unset($cli['comment']);
$cliAccepted = beacon_accept(json_encode($cli));
beacon_test($cliAccepted['ok'] === true, 'cli runtime and a missing comment are accepted');
beacon_test($cliAccepted['snapshot']['server_software'] === null, 'null server_software stays null');
beacon_test($cliAccepted['snapshot']['git_commit'] === null && $cliAccepted['snapshot']['git_branch'] === null, 'detached git is null');
beacon_test($cliAccepted['snapshot']['comment'] === '', 'missing comment stores empty');

$spacedBody = beacon_test_payload(array('install' => array(
    'wwwroot' => 'https://spaced.example.edu',
    'dirroot' => '/opt/tsugi',
    'owneremail' => ' Pat@Example.edu ',
)));
$spacedAccepted = beacon_accept(json_encode($spacedBody));
beacon_test($spacedAccepted['ok'] === true, 'spaced email matches the untrimmed preimage');
beacon_test($spacedAccepted['snapshot']['owneremail'] === ' Pat@Example.edu ', 'stored email keeps spaces and case');

$stampAccepted = beacon_accept(json_encode(beacon_test_payload(array('install' => array(
    'wwwroot' => 'https://stamps.example.edu',
    'dirroot' => '/var/www/a',
)))));
$otherAccepted = beacon_accept(json_encode(beacon_test_payload(array('install' => array(
    'wwwroot' => 'https://stamps.example.edu',
    'dirroot' => '/var/www/b',
)))));
$tieAccepted = beacon_accept(json_encode(beacon_test_payload(array('install' => array(
    'wwwroot' => 'https://stamps.example.edu',
    'dirroot' => '/var/www/c',
)))));
beacon_save($pdo, $stampAccepted['snapshot'], $stampAccepted['history'], 200, '2026-01-01T00:00:00Z');
beacon_save($pdo, $otherAccepted['snapshot'], $otherAccepted['history'], 200, '2026-06-01T00:00:00Z');
beacon_save($pdo, $stampAccepted['snapshot'], $stampAccepted['history'], 200, '2026-08-01T00:00:00Z');
beacon_save($pdo, $tieAccepted['snapshot'], $tieAccepted['history'], 200, '2026-08-01T00:00:00Z');
$stampRow = beacon_one($pdo, $stampAccepted['snapshot']['id']);
beacon_test($stampRow['created_at'] === '2026-01-01T00:00:00Z', 'created_at stays on the first write');
beacon_test($stampRow['updated_at'] === '2026-08-01T00:00:00Z', 'updated_at moves on the next write');
$stampHistory = beacon_history_for($pdo, $stampAccepted['snapshot']['id']);
beacon_test($stampHistory[0]['updated_at'] === '2026-08-01T00:00:00Z', 'history sorts by updated_at');
beacon_test($stampHistory[1]['created_at'] === '2026-01-01T00:00:00Z', 'older history row keeps its created_at');
$stampList = array();
foreach ( beacon_list($pdo, false) as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'https://stamps.example.edu' ) $stampList[] = $stampItem['dirroot'];
}
beacon_test($stampList === array('/var/www/c', '/var/www/a', '/var/www/b'), 'list sorts by updated_at then created_at');
$createdList = array();
foreach ( beacon_list($pdo, false, 'created_at') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'https://stamps.example.edu' ) $createdList[] = $stampItem['dirroot'];
}
beacon_test($createdList === array('/var/www/c', '/var/www/b', '/var/www/a'), 'list can sort by created_at then updated_at');
$createdAsc = array();
foreach ( beacon_list($pdo, false, 'created_at', 'asc') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'https://stamps.example.edu' ) $createdAsc[] = $stampItem['dirroot'];
}
beacon_test($createdAsc === array('/var/www/a', '/var/www/b', '/var/www/c'), 'created_at can sort ascending');
$foundDirs = array();
foreach ( beacon_list($pdo, false, 'updated_at', 'desc', '/var/www/b') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'https://stamps.example.edu' ) $foundDirs[] = $stampItem['dirroot'];
}
beacon_test($foundDirs === array('/var/www/b'), 'search matches a dirroot');
$caseHits = 0;
foreach ( beacon_list($pdo, false, 'wwwroot', 'asc', 'StAmPs.ExAmPle') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'https://stamps.example.edu' ) $caseHits++;
}
beacon_test($caseHits === 3, 'search is case insensitive');
beacon_test(count(beacon_list($pdo, false, 'updated_at', 'desc', 'no-such-beacon-xyz')) === 0, 'a miss returns no rows');
$fallback = array();
foreach ( beacon_list($pdo, false, 'not-a-column', 'sideways') as $stampItem ) {
    if ( $stampItem['wwwroot'] === 'https://stamps.example.edu' ) $fallback[] = $stampItem['dirroot'];
}
beacon_test($fallback === $stampList, 'an unknown sort falls back to updated_at descending');

$oldPath = tempnam(sys_get_temp_dir(), 'beaconold');
unlink($oldPath);
$old = new PDO('sqlite:' . $oldPath, null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$old->exec('CREATE TABLE beacon_schema (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    version INTEGER NOT NULL
)');
beacon_migrate_1($old);
$old->exec('INSERT INTO beacon_install (
    id, wwwroot, developer, local, lessons, server_below_minimum, schemas_json, reported_at, comment
) VALUES (
    \'0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef\',
    \'https://old.example.edu\', 0, 0, 0, 0, \'{}\', \'2024-05-01T00:00:00Z\', \'\'
)');
unset($old);
$upgraded = beacon_open($oldPath);
$oldRow = beacon_one($upgraded, '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef');
beacon_test($oldRow['created_at'] === '2024-05-01T00:00:00Z' && $oldRow['updated_at'] === '2024-05-01T00:00:00Z', 'migration 2 backfills both timestamps from reported_at');
unset($upgraded);
unlink($oldPath);

$pdo->exec('DELETE FROM beacon_history');
$pdo->exec('DELETE FROM beacon_install');
$insertOld = $pdo->prepare('INSERT INTO beacon_install (
        id, wwwroot, developer, local, lessons, server_below_minimum, schemas_json,
        reported_at, comment, created_at, updated_at
    ) VALUES (?, ?, 0, 0, 0, 0, \'{}\', ?, \'\', ?, ?)');
$insertHist = $pdo->prepare('INSERT INTO beacon_history (
        install_id, reported_at, software_version, recorded_overall, http_status, created_at, updated_at
    ) VALUES (?, ?, 1, 1, 200, ?, ?)');
for ( $n = 1; $n <= 4; $n++ ) {
    $oldId = str_repeat((string) $n, 64);
    $stamp = '2020-0' . $n . '-01T00:00:00Z';
    $insertOld->execute(array($oldId, 'https://old-' . $n . '.example.edu', $stamp, $stamp, $stamp));
    $insertHist->execute(array($oldId, $stamp, $stamp, $stamp));
}
beacon_prune($pdo, 3);
$kept = $pdo->query('SELECT wwwroot FROM beacon_install ORDER BY updated_at ASC')->fetchAll(PDO::FETCH_COLUMN);
beacon_test($kept === array(
    'https://old-2.example.edu',
    'https://old-3.example.edu',
    'https://old-4.example.edu',
), 'prune keeps the 3 most recent installs');
$goneHistory = (int) $pdo->query('SELECT COUNT(*) FROM beacon_history WHERE install_id = \'' . str_repeat('1', 64) . '\'')->fetchColumn();
beacon_test($goneHistory === 0, 'prune deletes history for the earliest install');
beacon_test((int) $pdo->query('SELECT COUNT(*) FROM beacon_install')->fetchColumn() === 3, 'prune stops at the cap');

unset($pdo);
$blob = file_get_contents($path);
beacon_test(strpos($blob, $withEmail) === false, 'database file does not contain the preimage');
beacon_test(strpos($blob, "pat@example.edu\n42") === false, 'database file does not contain the preimage line');
unlink($path);

if ( $failures > 0 ) {
    fwrite(STDERR, $failures . " failed\n");
    exit(1);
}
echo "ok\n";
exit(0);
