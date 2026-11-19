<?php

/**
 * MySQL store for Tsugi upgrade beacons.
 *
 * tsugi_beacon is the latest report for an install id. tsugi_beacon_history
 * is one row per accepted POST. The beacon code creates and updates these
 * tables. Tsugi's own upgrade does not know they exist. Unknown JSON fields
 * are ignored, and the install id preimage is never stored.
 */

const BEACON_SCHEMA_VERSION = 2;
const BEACON_IP_PLACE_DAYS = 21;
const BEACON_MAX_BODY = 5120;
const BEACON_MAX_COMMENT = 1024;
const BEACON_MAX_INSTALLS = 5000;
const BEACON_MAX_HISTORY = 20000;

function beacon_table($which) {
    global $BEACON_TABLE_PREFIX;
    $prefix = '';
    if ( isset($BEACON_TABLE_PREFIX) && is_string($BEACON_TABLE_PREFIX) && preg_match('/^[A-Za-z0-9_]+$/', $BEACON_TABLE_PREFIX) ) {
        $prefix = $BEACON_TABLE_PREFIX;
    }
    if ( $which === 'history' ) return $prefix . 'tsugi_beacon_history';
    if ( $which === 'schema' ) return $prefix . 'tsugi_beacon_schema';
    return $prefix . 'tsugi_beacon';
}

function beacon_open() {
    $pdo = beacon_connect();
    beacon_migrate($pdo);
    return $pdo;
}

function beacon_connect() {
    global $CFG;
    if ( getenv('TSUGI_BEACON_REFUSE_CONNECT') === '1' ) {
        throw new RuntimeException('beacon connect refused');
    }
    if ( ! isset($CFG) || ! is_object($CFG) || ! is_string($CFG->pdo ?? null) || $CFG->pdo === '' ) {
        $config = dirname(__DIR__) . '/tsugi/config.php';
        if ( ! is_file($config) ) {
            throw new RuntimeException('beacon database configuration');
        }
        require_once $config;
    }
    if ( ! isset($CFG) || ! is_object($CFG) || ! is_string($CFG->pdo ?? null) || $CFG->pdo === '' ) {
        throw new RuntimeException('beacon database configuration');
    }
    $dsn = $CFG->pdo;
    if ( stripos($dsn, 'charset=') === false ) $dsn .= ';charset=utf8mb4';
    return new PDO($dsn, (string) $CFG->dbuser, (string) $CFG->dbpass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));
}

function beacon_migrate(PDO $pdo) {
    $install = beacon_table('install');
    $history = beacon_table('history');
    $schema = beacon_table('schema');
    $pdo->exec('CREATE TABLE IF NOT EXISTS `' . $schema . '` (
        id TINYINT NOT NULL,
        version INT NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $pdo->exec('CREATE TABLE IF NOT EXISTS `' . $install . '` (
        id CHAR(64) NOT NULL,
        wwwroot VARCHAR(255) NOT NULL,
        servicename VARCHAR(200) NULL,
        ownername VARCHAR(200) NULL,
        owneremail VARCHAR(320) NULL,
        local TINYINT NOT NULL,
        tsugi_version VARCHAR(64) NULL,
        software_version BIGINT NULL,
        server_version VARCHAR(64) NULL,
        git_commit VARCHAR(64) NULL,
        git_branch VARCHAR(255) NULL,
        php VARCHAR(64) NULL,
        timezone VARCHAR(64) NULL,
        reported_at CHAR(20) NOT NULL,
        comment TEXT NOT NULL,
        created_at CHAR(20) NOT NULL,
        updated_at CHAR(20) NOT NULL,
        client_ip VARCHAR(45) NULL,
        client_host VARCHAR(255) NULL,
        upgrade_count INT NOT NULL DEFAULT 0,
        place_city VARCHAR(80) NULL,
        place_country CHAR(2) NULL,
        place_at CHAR(20) NULL,
        PRIMARY KEY (id),
        KEY beacon_wwwroot (wwwroot),
        KEY beacon_times (updated_at, created_at, id),
        KEY beacon_ip (client_ip)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $pdo->exec('CREATE TABLE IF NOT EXISTS `' . $history . '` (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        install_id CHAR(64) NOT NULL,
        reported_at CHAR(20) NOT NULL,
        software_version BIGINT NULL,
        git_commit VARCHAR(64) NULL,
        git_branch VARCHAR(255) NULL,
        http_status SMALLINT NOT NULL,
        received_at CHAR(20) NOT NULL,
        comment TEXT NOT NULL DEFAULT (\'\'),
        client_ip VARCHAR(45) NULL,
        client_host VARCHAR(255) NULL,
        php VARCHAR(64) NULL,
        server_version VARCHAR(64) NULL,
        place_city VARCHAR(80) NULL,
        place_country CHAR(2) NULL,
        place_at CHAR(20) NULL,
        PRIMARY KEY (id),
        KEY beacon_history_install (install_id),
        KEY beacon_history_received (received_at, id),
        KEY beacon_history_ip (client_ip),
        CONSTRAINT `' . $history . '_install` FOREIGN KEY (install_id) REFERENCES `' . $install . '` (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    if ( beacon_schema_version($pdo) < 1 ) beacon_schema_set($pdo, 1);
    if ( beacon_schema_version($pdo) < 2 ) beacon_migrate_2($pdo);
}

function beacon_migrate_2(PDO $pdo) {
    $history = beacon_table('history');
    if ( ! beacon_column_exists($pdo, $history, 'comment') ) {
        $pdo->exec('ALTER TABLE `' . $history . '` ADD COLUMN comment TEXT NOT NULL DEFAULT (\'\')');
    }
    beacon_schema_set($pdo, 2);
}

function beacon_column_exists(PDO $pdo, $table, $column) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute(array($table, $column));
    return (int) $stmt->fetchColumn() > 0;
}

function beacon_schema_version(PDO $pdo) {
    $row = $pdo->query('SELECT version FROM `' . beacon_table('schema') . '` WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    return $row ? (int) $row['version'] : 0;
}

function beacon_schema_set(PDO $pdo, $version) {
    $stmt = $pdo->prepare('INSERT INTO `' . beacon_table('schema') . '` (id, version) VALUES (1, :version)
        ON DUPLICATE KEY UPDATE version = VALUES(version)');
    $stmt->execute(array(':version' => (int) $version));
}

/**
 * @return array{ok:bool,error?:string,snapshot?:array,history?:array}
 */
function beacon_accept($raw) {
    if ( ! is_string($raw) || strlen($raw) < 1 || strlen($raw) > BEACON_MAX_BODY ) {
        return array('ok' => false, 'error' => 'json');
    }
    $data = json_decode($raw, false, 64);
    if ( ! is_object($data) || json_last_error() !== JSON_ERROR_NONE ) {
        return array('ok' => false, 'error' => 'json');
    }
    if ( ! property_exists($data, 'report_version') || ! is_int($data->report_version) || $data->report_version !== 1 ) {
        return array('ok' => false, 'error' => 'version');
    }
    if ( ! beacon_exact($data, 'marker', 'tsugi-upgrade-beacon') ) {
        return array('ok' => false, 'error' => 'marker');
    }

    $bad = false;
    $host = beacon_optional_string($data, 'host', 255, $bad);
    if ( $host === null ) $host = '';
    $owneremail = beacon_optional_string($data, 'owneremail', 320, $bad);
    $postedId = beacon_required_string($data, 'install_id', 64, $bad);
    if ( $bad || ! is_string($postedId) ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $emailPreimage = (is_string($owneremail) && $owneremail !== '') ? $owneremail : null;
    $expected = beacon_install_id($host, $emailPreimage);
    if ( ! preg_match('/^[0-9a-f]{64}$/', $postedId) || ! hash_equals($expected, $postedId) ) {
        return array('ok' => false, 'error' => 'id');
    }

    $databaseVersion = beacon_int($data, 'database_version', false, $bad);
    $reportedAt = beacon_reported_at(property_exists($data, 'reported_at') ? $data->reported_at : null);
    if ( $reportedAt === null ) $bad = true;
    $comment = beacon_comment($data, $bad);
    $git = new stdClass();
    $git->commit = property_exists($data, 'git_commit') ? $data->git_commit : null;
    $git->branch = property_exists($data, 'git_branch') ? $data->git_branch : null;
    $gitCommit = beacon_git_commit($git, $bad);
    $gitBranch = beacon_git_branch($git, $bad);
    $tsugiVersion = beacon_required_string($data, 'tsugi_version', 64, $bad);
    $phpVersion = beacon_required_string($data, 'php_version', 64, $bad);
    if ( $bad || ! is_string($tsugiVersion) || ! is_string($phpVersion) ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $snapshot = array(
        'id' => $postedId,
        'wwwroot' => $host,
        'servicename' => beacon_optional_string($data, 'servicename', 200, $bad),
        'ownername' => beacon_optional_string($data, 'ownername', 200, $bad),
        'owneremail' => $emailPreimage,
        'local' => beacon_wwwroot_is_local($host) ? 1 : 0,
        'tsugi_version' => $tsugiVersion,
        'software_version' => $databaseVersion,
        'server_version' => beacon_optional_string($data, 'mysql_version', 64, $bad),
        'git_commit' => $gitCommit,
        'git_branch' => $gitBranch,
        'php' => $phpVersion,
        'timezone' => beacon_optional_string($data, 'timezone', 64, $bad),
        'reported_at' => $reportedAt,
        'comment' => $comment,
    );
    if ( $bad ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $history = array(
        'install_id' => $postedId,
        'reported_at' => $reportedAt,
        'software_version' => $databaseVersion,
        'git_commit' => $gitCommit,
        'git_branch' => $gitBranch,
        'php' => $phpVersion,
        'server_version' => $snapshot['server_version'],
        'comment' => $comment,
    );
    return array('ok' => true, 'snapshot' => $snapshot, 'history' => $history);
}

function beacon_install_id($host, $owneremail) {
    // The last line of the preimage is not part of the posted comment.
    // Do not return it, log it, or put it on a page.
    $preimage = (string) $host;
    if ( is_string($owneremail) && $owneremail !== '' ) {
        $preimage .= "\n" . $owneremail;
    }
    return hash('sha256', $preimage . "\n" . '42');
}

function beacon_wwwroot_is_local($wwwroot) {
    $host = beacon_wwwroot_host($wwwroot);
    if ( $host === 'localhost' || $host === '127.0.0.1' || $host === '::1' ) return true;
    if ( strncmp($host, 'local.', 6) === 0 ) return true;
    $len = strlen($host);
    if ( $len >= 6 && substr($host, -6) === '.local' ) return true;
    if ( $len >= 10 && substr($host, -10) === '.localhost' ) return true;
    return false;
}

function beacon_wwwroot_host($wwwroot) {
    $host = parse_url($wwwroot, PHP_URL_HOST);
    if ( ! is_string($host) || $host === '' ) {
        $host = parse_url('http://' . $wwwroot, PHP_URL_HOST);
    }
    if ( ! is_string($host) ) return '';
    $host = strtolower($host);
    if ( strlen($host) >= 2 && $host[0] === '[' && substr($host, -1) === ']' ) {
        $host = substr($host, 1, -1);
    }
    return $host;
}

function beacon_save(PDO $pdo, array $snapshot, array $history, $httpStatus, $now = null, $client = null) {
    if ( ! is_string($now) || $now === '' ) $now = gmdate('Y-m-d\TH:i:s\Z');
    if ( ! is_array($client) ) $client = beacon_client_address();
    $install = beacon_table('install');
    $historyTable = beacon_table('history');
    $snapshot['created_at'] = $now;
    $snapshot['updated_at'] = $now;
    $snapshot['client_ip'] = $client['ip'];
    $snapshot['client_host'] = $client['host'];
    $history['received_at'] = $now;
    $history['client_ip'] = $client['ip'];
    $history['client_host'] = $client['host'];
    $pdo->beginTransaction();
    try {
    $sql = 'INSERT INTO `' . $install . '` (
            id, wwwroot, servicename, ownername, owneremail, local, tsugi_version,
            software_version, server_version, git_commit, git_branch, php, timezone,
            reported_at, comment, created_at, updated_at, client_ip, client_host, upgrade_count
        ) VALUES (
            :id, :wwwroot, :servicename, :ownername, :owneremail, :local, :tsugi_version,
            :software_version, :server_version, :git_commit, :git_branch, :php, :timezone,
            :reported_at, :comment, :created_at, :updated_at, :client_ip, :client_host, 1
        )
        ON DUPLICATE KEY UPDATE
            wwwroot = VALUES(wwwroot),
            servicename = VALUES(servicename),
            ownername = VALUES(ownername),
            owneremail = VALUES(owneremail),
            local = VALUES(local),
            tsugi_version = VALUES(tsugi_version),
            software_version = VALUES(software_version),
            server_version = VALUES(server_version),
            git_commit = VALUES(git_commit),
            git_branch = VALUES(git_branch),
            php = VALUES(php),
            timezone = VALUES(timezone),
            reported_at = VALUES(reported_at),
            comment = VALUES(comment),
            updated_at = VALUES(updated_at),
            client_ip = VALUES(client_ip),
            client_host = VALUES(client_host),
            upgrade_count = `' . $install . '`.upgrade_count + 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($snapshot);
    $hist = $pdo->prepare('INSERT INTO `' . $historyTable . '` (
            install_id, reported_at, software_version, git_commit, git_branch, http_status,
            received_at, client_ip, client_host, php, server_version, comment
        ) VALUES (
            :install_id, :reported_at, :software_version, :git_commit, :git_branch, :http_status,
            :received_at, :client_ip, :client_host, :php, :server_version, :comment
        )');
    $history['http_status'] = (int) $httpStatus;
    $hist->execute($history);
    beacon_prune($pdo);
    $pdo->commit();
    } catch ( Throwable $e ) {
        if ( $pdo->inTransaction() ) $pdo->rollBack();
        throw $e;
    }
}

/**
 * @return array{0:int,1:array}
 */
function beacon_delete(PDO $pdo, $id) {
    if ( ! is_string($id) || ! preg_match('/^[0-9a-f]{64}$/', $id) ) return false;
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM `' . beacon_table('history') . '` WHERE install_id = ?')->execute(array($id));
        $stmt = $pdo->prepare('DELETE FROM `' . beacon_table('install') . '` WHERE id = ?');
        $stmt->execute(array($id));
        $gone = $stmt->rowCount() > 0;
        $pdo->commit();
        return $gone;
    } catch ( Throwable $e ) {
        if ( $pdo->inTransaction() ) $pdo->rollBack();
        throw $e;
    }
}

function beacon_prune(PDO $pdo, $keep = BEACON_MAX_INSTALLS) {
    $keep = (int) $keep;
    if ( $keep < 1 ) $keep = BEACON_MAX_INSTALLS;
    $install = beacon_table('install');
    $historyTable = beacon_table('history');
    $count = (int) $pdo->query('SELECT COUNT(*) FROM `' . $install . '`')->fetchColumn();
    $extra = $count - $keep;
    if ( $extra > 0 ) {
        $ids = $pdo->query('SELECT id FROM `' . $install . '` ORDER BY updated_at ASC, created_at ASC, id ASC LIMIT ' . $extra)
            ->fetchAll(PDO::FETCH_COLUMN);
        foreach ( array_chunk($ids, 400) as $chunk ) {
            $marks = implode(',', array_fill(0, count($chunk), '?'));
            $pdo->prepare('DELETE FROM `' . $historyTable . '` WHERE install_id IN (' . $marks . ')')->execute($chunk);
            $pdo->prepare('DELETE FROM `' . $install . '` WHERE id IN (' . $marks . ')')->execute($chunk);
        }
    }
    beacon_prune_history($pdo);
}

function beacon_prune_history(PDO $pdo, $keep = BEACON_MAX_HISTORY) {
    $keep = (int) $keep;
    if ( $keep < 1 ) $keep = BEACON_MAX_HISTORY;
    $historyTable = beacon_table('history');
    $count = (int) $pdo->query('SELECT COUNT(*) FROM `' . $historyTable . '`')->fetchColumn();
    $extra = $count - $keep;
    if ( $extra < 1 ) return;
    $ids = $pdo->query('SELECT id FROM `' . $historyTable . '` ORDER BY received_at ASC, id ASC LIMIT ' . $extra)
        ->fetchAll(PDO::FETCH_COLUMN);
    if ( ! $ids ) return;
    foreach ( array_chunk($ids, 400) as $chunk ) {
        $marks = implode(',', array_fill(0, count($chunk), '?'));
        $pdo->prepare('DELETE FROM `' . $historyTable . '` WHERE id IN (' . $marks . ')')->execute($chunk);
    }
}

function beacon_client_address() {
    $ip = beacon_client_ip();
    return array(
        'ip' => $ip,
        'host' => beacon_reverse_host($ip),
    );
}

function beacon_client_ip() {
    $forwarded = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ( is_string($forwarded) ) {
        $forwarded = trim($forwarded);
        if ( filter_var($forwarded, FILTER_VALIDATE_IP) ) return $forwarded;
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if ( is_string($remote) && filter_var($remote, FILTER_VALIDATE_IP) ) return $remote;
    return null;
}

function beacon_reverse_host($ip) {
    if ( ! beacon_ip_routable($ip) ) return null;
    $previous = getenv('RES_OPTIONS');
    putenv('RES_OPTIONS=timeout:1 attempts:1');
    $name = @gethostbyaddr($ip);
    if ( $previous === false ) {
        putenv('RES_OPTIONS');
    } else {
        putenv('RES_OPTIONS=' . $previous);
    }
    return beacon_clean_host($ip, $name);
}

function beacon_clean_host($ip, $name) {
    if ( ! is_string($name) ) return null;
    $name = rtrim($name, '.');
    if ( $name === '' || $name === $ip || strlen($name) > 255 ) return null;
    if ( preg_match('/[\x00-\x1f\x7f]/', $name) ) return null;
    return $name;
}

function beacon_ip_places(PDO $pdo, array $ips, $now = null, $fetch = null) {
    $places = array();
    $fetched = 0;
    foreach ( $ips as $ip ) {
        if ( ! is_string($ip) || $ip === '' || array_key_exists($ip, $places) ) continue;
        $cached = beacon_ip_place_cached($pdo, $ip, $now);
        if ( $cached !== null ) {
            $places[$ip] = $cached;
            continue;
        }
        if ( $fetched >= 4 ) continue;
        $places[$ip] = beacon_ip_place($pdo, $ip, $now, $fetch);
        if ( beacon_ip_routable($ip) ) $fetched++;
    }
    return $places;
}

function beacon_ip_place(PDO $pdo, $ip, $now = null, $fetch = null) {
    if ( ! beacon_ip_routable($ip) ) return null;
    if ( ! is_string($now) || $now === '' ) $now = gmdate('Y-m-d\TH:i:s\Z');
    $cached = beacon_ip_place_cached($pdo, $ip, $now);
    if ( $cached !== null ) return $cached;
    if ( $fetch === null ) $fetch = 'beacon_ipwho_fetch';
    $data = $fetch($ip);
    if ( ! is_array($data) ) return null;
    $city = null;
    $country = null;
    if ( ($data['success'] ?? false) ) {
        $city = beacon_ip_place_text($data['city'] ?? null, 80);
        $country = beacon_ip_place_text($data['country_code'] ?? null, 2);
        if ( is_string($country) ) $country = strtoupper($country);
        if ( ! is_string($country) || ! preg_match('/^[A-Z]{2}$/', $country) ) $country = null;
    }
    beacon_ip_place_store($pdo, $ip, $city, $country, $now);
    return array('city' => $city, 'country_code' => $country);
}

function beacon_ip_place_store(PDO $pdo, $ip, $city, $country, $now) {
    $sql = 'UPDATE `%s` SET place_city = :city, place_country = :country, place_at = :place_at WHERE client_ip = :ip';
    $params = array(
        ':city' => $city,
        ':country' => $country,
        ':place_at' => $now,
        ':ip' => $ip,
    );
    $pdo->prepare(sprintf($sql, beacon_table('install')))->execute($params);
    $pdo->prepare(sprintf($sql, beacon_table('history')))->execute($params);
}

function beacon_ip_place_cached(PDO $pdo, $ip, $now = null) {
    if ( ! beacon_ip_routable($ip) ) return null;
    if ( ! is_string($now) || $now === '' ) $now = gmdate('Y-m-d\TH:i:s\Z');
    $install = beacon_table('install');
    $history = beacon_table('history');
    $stmt = $pdo->prepare('SELECT place_city AS city, place_country AS country_code, place_at AS looked_up_at FROM (
            SELECT place_city, place_country, place_at FROM `' . $install . '`
                WHERE client_ip = ? AND place_at IS NOT NULL
            UNION ALL
            SELECT place_city, place_country, place_at FROM `' . $history . '`
                WHERE client_ip = ? AND place_at IS NOT NULL
        ) places ORDER BY looked_up_at DESC LIMIT 1');
    $stmt->execute(array($ip, $ip));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ( ! $row || ! beacon_ip_place_fresh($row['looked_up_at'], $now) ) return null;
    return array(
        'city' => $row['city'],
        'country_code' => $row['country_code'],
    );
}

function beacon_ip_place_fresh($lookedUpAt, $now) {
    $then = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', (string) $lookedUpAt, new DateTimeZone('UTC'));
    $current = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', (string) $now, new DateTimeZone('UTC'));
    if ( ! $then || ! $current ) return false;
    $age = $current->getTimestamp() - $then->getTimestamp();
    return $age < BEACON_IP_PLACE_DAYS * 24 * 3600;
}

function beacon_ip_routable($ip) {
    if ( ! is_string($ip) ) return false;
    $ip = trim($ip);
    if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
        if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false ) {
            return false;
        }
        $long = ip2long($ip);
        if ( $long === false ) return false;
        // Multicast, carrier-grade NAT, and the documentation ranges.
        if ( ($long & 0xF0000000) === 0xE0000000 ) return false;
        if ( ($long & 0xFFC00000) === 0x64400000 ) return false;
        if ( ($long & 0xFFFFFF00) === 0xC0000200 ) return false;
        if ( ($long & 0xFFFFFF00) === 0xC6336400 ) return false;
        if ( ($long & 0xFFFFFF00) === 0xCB007100 ) return false;
        return true;
    }
    if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false ) return false;
    if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false ) {
        return false;
    }
    $bin = inet_pton($ip);
    if ( ! is_string($bin) || strlen($bin) !== 16 ) return false;
    if ( ord($bin[0]) === 0xff ) return false;
    if ( substr($bin, 0, 4) === "\x20\x01\x0d\xb8" ) return false;
    return true;
}

function beacon_ip_place_text($value, $max) {
    if ( ! is_string($value) ) return null;
    $value = trim($value);
    if ( $value === '' || strlen($value) > $max ) return null;
    if ( preg_match('/[\x00-\x1f\x7f]/', $value) ) return null;
    return $value;
}

function beacon_ipwho_fetch($ip) {
    $url = 'https://ipwho.is/' . urlencode($ip);
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 2,
            'max_redirects' => 2,
            'header' => "Accept: application/json\r\nUser-Agent: tsugi-beacon\r\n",
        ),
    ));
    $raw = @file_get_contents($url, false, $context);
    if ( ! is_string($raw) || $raw === '' || strlen($raw) > 20000 ) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function beacon_read_body() {
    $declared = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ( $declared > BEACON_MAX_BODY ) return false;
    $stream = fopen('php://input', 'rb');
    $raw = $stream ? stream_get_contents($stream, BEACON_MAX_BODY + 1) : '';
    if ( is_resource($stream) ) fclose($stream);
    if ( ! is_string($raw) || strlen($raw) > BEACON_MAX_BODY ) return false;
    return $raw;
}

function beacon_handle($method, $raw, $pdo = null) {
    if ( $method !== 'POST' ) {
        return array(405, array('error' => 'method not allowed'));
    }
    // Decide the report is storeable before opening MySQL. A flood of bad
    // bodies must not take a connection.
    $accepted = beacon_accept($raw);
    if ( ! $accepted['ok'] ) {
        return array(400, array('error' => 'bad request'));
    }
    $client = beacon_client_address();
    $status = 200;
    if ( ! $pdo instanceof PDO ) $pdo = beacon_open();
    beacon_save($pdo, $accepted['snapshot'], $accepted['history'], $status, null, $client);
    return array($status, array('ok' => true));
}

function beacon_list_columns() {
    return array(
        'wwwroot' => 'text',
        'install_count' => 'number',
        'servicename' => 'text',
        'tsugi_version' => 'version',
        'software_version' => 'number',
        'php' => 'text',
        'server_version' => 'text',
        'upgrade_count' => 'number',
        'updated_at' => 'text',
        'created_at' => 'text',
        'client_ip' => 'text',
        'git' => 'text',
        'owner' => 'text',
    );
}

function beacon_list(PDO $pdo, $showLocal, $sort = 'updated_at', $dir = 'desc', $search = '') {
    $columns = beacon_list_columns();
    if ( ! isset($columns[$sort]) ) $sort = 'updated_at';
    if ( $dir !== 'asc' && $dir !== 'desc' ) $dir = 'desc';
    $search = beacon_search_needle($search);

    $sql = 'SELECT id, wwwroot, servicename, ownername, owneremail, local, tsugi_version,
            software_version, server_version, git_commit, git_branch, php, timezone,
            reported_at, comment, created_at, updated_at, client_ip, client_host, upgrade_count
        FROM `' . beacon_table('install') . '`';
    if ( ! $showLocal ) $sql .= ' WHERE local = 0';
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    if ( $search !== '' ) {
        $rows = array_values(array_filter($rows, function ($row) use ($search) {
            return beacon_row_matches($row, $search);
        }));
    }
    $counts = array();
    foreach ( $rows as $row ) {
        $host = (string) $row['wwwroot'];
        if ( ! isset($counts[$host]) ) $counts[$host] = 0;
        $counts[$host]++;
    }
    foreach ( $rows as $i => $row ) {
        $rows[$i]['install_count'] = $counts[(string) $row['wwwroot']];
    }
    usort($rows, function ($a, $b) use ($sort, $dir) {
        return beacon_row_cmp($a, $b, $sort, $dir);
    });
    return $rows;
}

function beacon_search_needle($search) {
    $search = trim((string) $search);
    if ( strlen($search) > 200 ) $search = substr($search, 0, 200);
    return $search;
}

function beacon_row_matches(array $row, $search) {
    $needle = beacon_lower($search);
    if ( $needle === '' ) return true;
    $parts = array(
        $row['wwwroot'], $row['servicename'], $row['ownername'], $row['owneremail'],
        $row['tsugi_version'], $row['software_version'], $row['php'], $row['server_version'],
        $row['timezone'], $row['git_commit'], $row['git_branch'], $row['reported_at'],
        $row['created_at'], $row['updated_at'], $row['comment'],
        $row['client_ip'], $row['client_host'],
    );
    $haystack = beacon_lower(implode("\n", array_map(function ($value) {
        return (string) $value;
    }, $parts)));
    return strpos($haystack, $needle) !== false;
}

function beacon_lower($value) {
    $value = (string) $value;
    if ( function_exists('mb_strtolower') ) return mb_strtolower($value, 'UTF-8');
    return strtolower($value);
}

function beacon_row_cmp(array $a, array $b, $sort, $dir) {
    $va = beacon_sort_value($a, $sort);
    $vb = beacon_sort_value($b, $sort);
    $aMissing = beacon_sort_missing($va);
    $bMissing = beacon_sort_missing($vb);
    if ( $aMissing || $bMissing ) {
        if ( ! ($aMissing && $bMissing) ) return $aMissing ? 1 : -1;
    } else {
        $kind = beacon_list_columns()[$sort];
        if ( $kind === 'number' ) {
            $cmp = ((int) $va) <=> ((int) $vb);
        } else if ( $kind === 'version' ) {
            $cmp = version_compare((string) $va, (string) $vb);
        } else {
            $cmp = strcasecmp((string) $va, (string) $vb);
        }
        if ( $dir === 'desc' ) $cmp = -$cmp;
        if ( $cmp !== 0 ) return $cmp;
    }
    $created = strcmp((string) $b['created_at'], (string) $a['created_at']);
    if ( $created !== 0 ) return $created;
    return strcmp((string) $a['id'], (string) $b['id']);
}

function beacon_sort_value(array $row, $sort) {
    if ( $sort === 'upgrade_count' ) return (int) $row['upgrade_count'];
    if ( $sort === 'git' ) {
        return trim((string) ($row['git_commit'] ?? '') . ' ' . (string) ($row['git_branch'] ?? ''));
    }
    if ( $sort === 'owner' ) {
        return trim((string) ($row['ownername'] ?? '') . ' ' . (string) ($row['owneremail'] ?? ''));
    }
    if ( ! array_key_exists($sort, $row) ) return '';
    return $row[$sort];
}

function beacon_sort_missing($value) {
    return $value === null || $value === '';
}

function beacon_one(PDO $pdo, $id) {
    if ( ! is_string($id) || ! preg_match('/^[0-9a-f]{64}$/', $id) ) return null;
    $stmt = $pdo->prepare('SELECT id, wwwroot, servicename, ownername, owneremail, local, tsugi_version,
            software_version, server_version, git_commit, git_branch, php, timezone,
            reported_at, comment, created_at, updated_at, client_ip, client_host, upgrade_count
        FROM `' . beacon_table('install') . '` WHERE id = ?');
    $stmt->execute(array($id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row : null;
}

function beacon_history_for(PDO $pdo, $id) {
    if ( ! is_string($id) || ! preg_match('/^[0-9a-f]{64}$/', $id) ) return array();
    $stmt = $pdo->prepare('SELECT reported_at, software_version, git_commit, git_branch, http_status,
            received_at, client_ip, client_host, php, server_version, comment
        FROM `' . beacon_table('history') . '` WHERE install_id = ? ORDER BY received_at DESC, id DESC');
    $stmt->execute(array($id));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function beacon_exact($obj, $key, $expected) {
    return is_object($obj) && property_exists($obj, $key) && is_string($obj->$key) && $obj->$key === $expected;
}

function beacon_required_string($obj, $key, $max, &$bad) {
    if ( ! is_object($obj) || ! property_exists($obj, $key) || ! is_string($obj->$key) ) {
        $bad = true;
        return null;
    }
    if ( strlen($obj->$key) > $max || strpos($obj->$key, "\0") !== false ) {
        $bad = true;
        return null;
    }
    return $obj->$key;
}

function beacon_optional_string($obj, $key, $max, &$bad) {
    if ( ! is_object($obj) || ! property_exists($obj, $key) || $obj->$key === null || $obj->$key === false ) {
        return null;
    }
    if ( ! is_string($obj->$key) || strlen($obj->$key) > $max || strpos($obj->$key, "\0") !== false ) {
        $bad = true;
        return null;
    }
    return $obj->$key;
}

function beacon_int($obj, $key, $required, &$bad) {
    if ( ! is_object($obj) || ! property_exists($obj, $key) || $obj->$key === null ) {
        if ( $required ) $bad = true;
        return null;
    }
    if ( ! is_int($obj->$key) ) {
        $bad = true;
        return null;
    }
    return $obj->$key;
}

function beacon_reported_at($value) {
    if ( ! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $value) ) {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $value, new DateTimeZone('UTC'));
    if ( ! $dt || $dt->format('Y-m-d\TH:i:s\Z') !== $value ) return null;
    return $value;
}

function beacon_comment($data, &$bad) {
    if ( ! is_object($data) || ! property_exists($data, 'comment') || $data->comment === null ) {
        return '';
    }
    if ( ! is_string($data->comment) || strlen($data->comment) > BEACON_MAX_COMMENT || strpos($data->comment, "\0") !== false ) {
        $bad = true;
        return '';
    }
    return $data->comment;
}

function beacon_git_commit($git, &$bad) {
    if ( ! property_exists($git, 'commit') || $git->commit === null ) return null;
    if ( ! is_string($git->commit) || ! preg_match('/^[0-9a-fA-F]{1,64}$/', $git->commit) ) {
        $bad = true;
        return null;
    }
    return strtolower($git->commit);
}

function beacon_git_branch($git, &$bad) {
    if ( ! property_exists($git, 'branch') || $git->branch === null ) return null;
    if ( ! is_string($git->branch) || $git->branch === '' || strlen($git->branch) > 255 ) {
        $bad = true;
        return null;
    }
    if ( preg_match('/[\x00-\x1f\x7f]/', $git->branch) ) {
        $bad = true;
        return null;
    }
    return $git->branch;
}
