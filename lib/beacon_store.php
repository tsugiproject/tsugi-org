<?php

/**
 * SQLite store for Tsugi upgrade beacons.
 *
 * The snapshot is the latest report for an install id. History is one row
 * per accepted POST. The install id is checked here and then discarded
 * inputs are not kept: unknown JSON fields are ignored, and the id preimage
 * is never stored.
 */

const BEACON_SCHEMA_VERSION = 2;
const BEACON_MAX_BODY = 262144;
const BEACON_MAX_SCHEMAS = 300;

function beacon_sqlite_path() {
    $fromEnv = getenv('TSUGI_BEACON_SQLITE');
    if ( is_string($fromEnv) && $fromEnv !== '' ) return $fromEnv;
    return dirname(__DIR__) . '/data/beacon.sqlite';
}

function beacon_open($path = null) {
    if ( $path === null ) $path = beacon_sqlite_path();
    $dir = dirname($path);
    $madeDir = false;
    if ( ! is_dir($dir) ) {
        if ( ! mkdir($dir, 0700, true) && ! is_dir($dir) ) {
            throw new RuntimeException('beacon data directory');
        }
        $madeDir = true;
    }
    // Keep a directory we created private. Do not chmod a shared parent
    // such as /tmp when the path is overridden.
    if ( $madeDir || basename($dir) === 'data' ) chmod($dir, 0700);
    $pdo = new PDO('sqlite:' . $path, null, null, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    beacon_migrate($pdo);
    if ( is_file($path) ) chmod($path, 0600);
    return $pdo;
}

function beacon_migrate(PDO $pdo) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS beacon_schema (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        version INTEGER NOT NULL
    )');
    $row = $pdo->query('SELECT version FROM beacon_schema WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    $version = $row ? (int) $row['version'] : 0;
    if ( $version < 1 ) {
        beacon_migrate_1($pdo);
        $version = 1;
    }
    if ( $version < 2 ) beacon_migrate_2($pdo);
}

function beacon_migrate_1(PDO $pdo) {
    $pdo->beginTransaction();
    $pdo->exec('CREATE TABLE IF NOT EXISTS beacon_install (
        id TEXT PRIMARY KEY,
        wwwroot TEXT NOT NULL,
        apphome TEXT,
        dirroot TEXT,
        servicename TEXT,
        servicedesc TEXT,
        ownername TEXT,
        owneremail TEXT,
        developer INTEGER NOT NULL,
        local INTEGER NOT NULL,
        lessons INTEGER NOT NULL,
        tsugi_version TEXT,
        minimum_php TEXT,
        minimum_mysql TEXT,
        software_version INTEGER,
        highest_migration INTEGER,
        highest_migration_path TEXT,
        recorded_overall INTEGER,
        server_version TEXT,
        server_below_minimum INTEGER NOT NULL,
        driver TEXT,
        prefix TEXT,
        schemas_json TEXT NOT NULL,
        git_commit TEXT,
        git_branch TEXT,
        php TEXT,
        sapi TEXT,
        os TEXT,
        os_release TEXT,
        hostname TEXT,
        timezone TEXT,
        server_software TEXT,
        reported_at TEXT NOT NULL,
        comment TEXT NOT NULL
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS beacon_install_wwwroot ON beacon_install (wwwroot)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS beacon_install_reported ON beacon_install (reported_at)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS beacon_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        install_id TEXT NOT NULL,
        reported_at TEXT NOT NULL,
        software_version INTEGER,
        recorded_overall INTEGER,
        git_commit TEXT,
        git_branch TEXT,
        http_status INTEGER NOT NULL,
        FOREIGN KEY (install_id) REFERENCES beacon_install(id)
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS beacon_history_install ON beacon_history (install_id)');
    beacon_schema_set($pdo, 1);
    $pdo->commit();
}

function beacon_migrate_2(PDO $pdo) {
    $pdo->beginTransaction();
    $pdo->exec('ALTER TABLE beacon_install ADD COLUMN created_at TEXT NOT NULL DEFAULT \'\'');
    $pdo->exec('ALTER TABLE beacon_install ADD COLUMN updated_at TEXT NOT NULL DEFAULT \'\'');
    $pdo->exec('ALTER TABLE beacon_history ADD COLUMN created_at TEXT NOT NULL DEFAULT \'\'');
    $pdo->exec('ALTER TABLE beacon_history ADD COLUMN updated_at TEXT NOT NULL DEFAULT \'\'');
    $pdo->exec('UPDATE beacon_install SET created_at = reported_at WHERE created_at = \'\'');
    $pdo->exec('UPDATE beacon_install SET updated_at = reported_at WHERE updated_at = \'\'');
    $pdo->exec('UPDATE beacon_history SET created_at = reported_at WHERE created_at = \'\'');
    $pdo->exec('UPDATE beacon_history SET updated_at = reported_at WHERE updated_at = \'\'');
    $pdo->exec('CREATE INDEX IF NOT EXISTS beacon_install_times ON beacon_install (updated_at, created_at)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS beacon_history_times ON beacon_history (updated_at, created_at)');
    beacon_schema_set($pdo, 2);
    $pdo->commit();
}

function beacon_schema_set(PDO $pdo, $version) {
    $stmt = $pdo->prepare('INSERT INTO beacon_schema (id, version) VALUES (1, :version)
        ON CONFLICT(id) DO UPDATE SET version = excluded.version');
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
    if ( ! beacon_exact($data, 'event', 'database_upgrade') ) {
        return array('ok' => false, 'error' => 'event');
    }
    if ( ! beacon_exact($data, 'source', 'admin/upgrade.php') ) {
        return array('ok' => false, 'error' => 'source');
    }

    $install = beacon_object($data, 'install');
    $software = beacon_object($data, 'software');
    $database = beacon_object($data, 'database');
    $git = beacon_object($data, 'git');
    $runtime = beacon_object($data, 'runtime');
    if ( $install === null || $software === null || $database === null || $git === null || $runtime === null ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $bad = false;
    $wwwroot = beacon_required_string($install, 'wwwroot', 2000, $bad);
    $dirrootValue = beacon_optional_string($install, 'dirroot', 2000, $bad);
    $owneremail = beacon_optional_string($install, 'owneremail', 320, $bad);
    $postedId = beacon_required_string($install, 'id', 64, $bad);
    if ( $bad || ! is_string($wwwroot) || ! is_string($postedId) ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $dirrootPreimage = $dirrootValue === null ? '' : $dirrootValue;
    $emailPreimage = (is_string($owneremail) && $owneremail !== '') ? $owneremail : null;
    $expected = beacon_install_id($wwwroot, $dirrootPreimage, $emailPreimage);
    if ( ! preg_match('/^[0-9a-f]{64}$/', $postedId) || ! hash_equals($expected, $postedId) ) {
        return array('ok' => false, 'error' => 'id');
    }

    $developer = beacon_bool($install, 'developer', $bad);
    $lessons = beacon_bool($install, 'lessons', $bad);
    beacon_bool($install, 'local', $bad);
    $serverBelow = beacon_bool($database, 'server_below_minimum', $bad);
    $softwareVersion = beacon_int($database, 'software_version', true, $bad);
    $highestMigration = beacon_int($database, 'highest_migration', true, $bad);
    $recordedOverall = beacon_int($database, 'recorded_overall', false, $bad);
    $reportedAt = beacon_reported_at(property_exists($data, 'reported_at') ? $data->reported_at : null);
    if ( $reportedAt === null ) $bad = true;
    $comment = beacon_comment($data, $bad);
    $schemas = beacon_schemas($database, $bad);
    $gitCommit = beacon_git_commit($git, $bad);
    $gitBranch = beacon_git_branch($git, $bad);
    if ( $bad || $schemas === null ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $schemasJson = json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ( ! is_string($schemasJson) ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $snapshot = array(
        'id' => $postedId,
        'wwwroot' => $wwwroot,
        'apphome' => beacon_optional_string($install, 'apphome', 2000, $bad),
        'dirroot' => $dirrootValue,
        'servicename' => beacon_optional_string($install, 'servicename', 200, $bad),
        'servicedesc' => beacon_optional_string($install, 'servicedesc', 2000, $bad),
        'ownername' => beacon_optional_string($install, 'ownername', 200, $bad),
        'owneremail' => $emailPreimage,
        'developer' => $developer ? 1 : 0,
        'local' => beacon_wwwroot_is_local($wwwroot) ? 1 : 0,
        'lessons' => $lessons ? 1 : 0,
        'tsugi_version' => beacon_optional_string($software, 'tsugi_version', 64, $bad),
        'minimum_php' => beacon_optional_string($software, 'minimum_php', 64, $bad),
        'minimum_mysql' => beacon_optional_string($software, 'minimum_mysql', 64, $bad),
        'software_version' => $softwareVersion,
        'highest_migration' => $highestMigration,
        'highest_migration_path' => beacon_optional_string($database, 'highest_migration_path', 300, $bad),
        'recorded_overall' => $recordedOverall,
        'server_version' => beacon_optional_string($database, 'server_version', 64, $bad),
        'server_below_minimum' => $serverBelow ? 1 : 0,
        'driver' => beacon_optional_string($database, 'driver', 32, $bad),
        'prefix' => beacon_optional_string($database, 'prefix', 64, $bad),
        'schemas_json' => $schemasJson,
        'git_commit' => $gitCommit,
        'git_branch' => $gitBranch,
        'php' => beacon_optional_string($runtime, 'php', 64, $bad),
        'sapi' => beacon_optional_string($runtime, 'sapi', 64, $bad),
        'os' => beacon_optional_string($runtime, 'os', 64, $bad),
        'os_release' => beacon_optional_string($runtime, 'os_release', 128, $bad),
        'hostname' => beacon_optional_string($runtime, 'hostname', 255, $bad),
        'timezone' => beacon_optional_string($runtime, 'timezone', 64, $bad),
        'server_software' => beacon_optional_string($runtime, 'server_software', 255, $bad),
        'reported_at' => $reportedAt,
        'comment' => $comment,
    );
    if ( $bad ) {
        return array('ok' => false, 'error' => 'shape');
    }

    $history = array(
        'install_id' => $postedId,
        'reported_at' => $reportedAt,
        'software_version' => $softwareVersion,
        'recorded_overall' => $recordedOverall,
        'git_commit' => $gitCommit,
        'git_branch' => $gitBranch,
    );
    return array('ok' => true, 'snapshot' => $snapshot, 'history' => $history);
}

function beacon_install_id($wwwroot, $dirroot, $owneremail) {
    // The last line of the preimage is not part of the posted comment.
    // Do not return it, log it, or put it on a page.
    if ( is_string($owneremail) && $owneremail !== '' ) {
        $preimage = $wwwroot . "\n" . $dirroot . "\n" . $owneremail . "\n" . '42';
    } else {
        $preimage = $wwwroot . "\n" . $dirroot . "\n" . '42';
    }
    return hash('sha256', $preimage);
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

function beacon_save(PDO $pdo, array $snapshot, array $history, $httpStatus, $now = null) {
    if ( ! is_string($now) || $now === '' ) $now = gmdate('Y-m-d\TH:i:s\Z');
    $snapshot['created_at'] = $now;
    $snapshot['updated_at'] = $now;
    $history['created_at'] = $now;
    $history['updated_at'] = $now;
    $pdo->beginTransaction();
    try {
    $sql = 'INSERT INTO beacon_install (
            id, wwwroot, apphome, dirroot, servicename, servicedesc, ownername, owneremail,
            developer, local, lessons, tsugi_version, minimum_php, minimum_mysql,
            software_version, highest_migration, highest_migration_path, recorded_overall,
            server_version, server_below_minimum, driver, prefix, schemas_json,
            git_commit, git_branch, php, sapi, os, os_release, hostname, timezone,
            server_software, reported_at, comment, created_at, updated_at
        ) VALUES (
            :id, :wwwroot, :apphome, :dirroot, :servicename, :servicedesc, :ownername, :owneremail,
            :developer, :local, :lessons, :tsugi_version, :minimum_php, :minimum_mysql,
            :software_version, :highest_migration, :highest_migration_path, :recorded_overall,
            :server_version, :server_below_minimum, :driver, :prefix, :schemas_json,
            :git_commit, :git_branch, :php, :sapi, :os, :os_release, :hostname, :timezone,
            :server_software, :reported_at, :comment, :created_at, :updated_at
        )
        ON CONFLICT(id) DO UPDATE SET
            wwwroot = excluded.wwwroot,
            apphome = excluded.apphome,
            dirroot = excluded.dirroot,
            servicename = excluded.servicename,
            servicedesc = excluded.servicedesc,
            ownername = excluded.ownername,
            owneremail = excluded.owneremail,
            developer = excluded.developer,
            local = excluded.local,
            lessons = excluded.lessons,
            tsugi_version = excluded.tsugi_version,
            minimum_php = excluded.minimum_php,
            minimum_mysql = excluded.minimum_mysql,
            software_version = excluded.software_version,
            highest_migration = excluded.highest_migration,
            highest_migration_path = excluded.highest_migration_path,
            recorded_overall = excluded.recorded_overall,
            server_version = excluded.server_version,
            server_below_minimum = excluded.server_below_minimum,
            driver = excluded.driver,
            prefix = excluded.prefix,
            schemas_json = excluded.schemas_json,
            git_commit = excluded.git_commit,
            git_branch = excluded.git_branch,
            php = excluded.php,
            sapi = excluded.sapi,
            os = excluded.os,
            os_release = excluded.os_release,
            hostname = excluded.hostname,
            timezone = excluded.timezone,
            server_software = excluded.server_software,
            reported_at = excluded.reported_at,
            comment = excluded.comment,
            updated_at = excluded.updated_at';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($snapshot);
    $hist = $pdo->prepare('INSERT INTO beacon_history (
            install_id, reported_at, software_version, recorded_overall,
            git_commit, git_branch, http_status, created_at, updated_at
        ) VALUES (
            :install_id, :reported_at, :software_version, :recorded_overall,
            :git_commit, :git_branch, :http_status, :created_at, :updated_at
        )');
    $history['http_status'] = (int) $httpStatus;
    $hist->execute($history);
    $pdo->commit();
    } catch ( Throwable $e ) {
        if ( $pdo->inTransaction() ) $pdo->rollBack();
        throw $e;
    }
}

/**
 * @return array{0:int,1:array}
 */
function beacon_handle($method, $raw, $pdo = null) {
    if ( $method !== 'POST' ) {
        return array(405, array('error' => 'method not allowed'));
    }
    $accepted = beacon_accept($raw);
    if ( ! $accepted['ok'] ) {
        return array(400, array('error' => 'bad request'));
    }
    $status = 200;
    if ( ! $pdo instanceof PDO ) $pdo = beacon_open();
    beacon_save($pdo, $accepted['snapshot'], $accepted['history'], $status);
    return array($status, array('ok' => true));
}

function beacon_list(PDO $pdo, $showLocal) {
    $sql = 'SELECT id, wwwroot, apphome, dirroot, servicename, servicedesc, ownername, owneremail,
            developer, local, lessons, tsugi_version, minimum_php, minimum_mysql,
            software_version, highest_migration, highest_migration_path, recorded_overall,
            server_version, server_below_minimum, driver, prefix, schemas_json,
            git_commit, git_branch, php, sapi, os, os_release, hostname, timezone,
            server_software, reported_at, comment, created_at, updated_at,
            (SELECT COUNT(*) FROM beacon_history h WHERE h.install_id = beacon_install.id) AS upgrade_count
        FROM beacon_install';
    if ( ! $showLocal ) $sql .= ' WHERE local = 0';
    $sql .= ' ORDER BY updated_at DESC, created_at DESC, wwwroot ASC';
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function beacon_one(PDO $pdo, $id) {
    if ( ! is_string($id) || ! preg_match('/^[0-9a-f]{64}$/', $id) ) return null;
    $stmt = $pdo->prepare('SELECT id, wwwroot, apphome, dirroot, servicename, servicedesc, ownername, owneremail,
            developer, local, lessons, tsugi_version, minimum_php, minimum_mysql,
            software_version, highest_migration, highest_migration_path, recorded_overall,
            server_version, server_below_minimum, driver, prefix, schemas_json,
            git_commit, git_branch, php, sapi, os, os_release, hostname, timezone,
            server_software, reported_at, comment, created_at, updated_at,
            (SELECT COUNT(*) FROM beacon_history h WHERE h.install_id = beacon_install.id) AS upgrade_count
        FROM beacon_install WHERE id = ?');
    $stmt->execute(array($id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row : null;
}

function beacon_history_for(PDO $pdo, $id) {
    if ( ! is_string($id) || ! preg_match('/^[0-9a-f]{64}$/', $id) ) return array();
    $stmt = $pdo->prepare('SELECT reported_at, software_version, recorded_overall, git_commit, git_branch, http_status,
            created_at, updated_at
        FROM beacon_history WHERE install_id = ? ORDER BY updated_at DESC, created_at DESC, id DESC');
    $stmt->execute(array($id));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function beacon_exact($obj, $key, $expected) {
    return is_object($obj) && property_exists($obj, $key) && is_string($obj->$key) && $obj->$key === $expected;
}

function beacon_object($obj, $key) {
    if ( ! is_object($obj) || ! property_exists($obj, $key) || ! is_object($obj->$key) ) return null;
    return $obj->$key;
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

function beacon_bool($obj, $key, &$bad) {
    if ( ! is_object($obj) || ! property_exists($obj, $key) || ! is_bool($obj->$key) ) {
        $bad = true;
        return false;
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
    if ( ! is_string($data->comment) || strlen($data->comment) > 4000 || strpos($data->comment, "\0") !== false ) {
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

function beacon_schemas($database, &$bad) {
    if ( ! property_exists($database, 'schemas') || $database->schemas === null ) {
        return new stdClass();
    }
    $schemas = $database->schemas;
    if ( ! is_object($schemas) ) {
        $bad = true;
        return null;
    }
    $clean = new stdClass();
    $n = 0;
    foreach ( $schemas as $path => $version ) {
        $n++;
        if ( $n > BEACON_MAX_SCHEMAS || ! is_string($path) || $path === '' || strlen($path) > 300 || strpos($path, "\0") !== false ) {
            $bad = true;
            return null;
        }
        if ( ! is_int($version) ) {
            $bad = true;
            return null;
        }
        $clean->$path = $version;
    }
    return $clean;
}
