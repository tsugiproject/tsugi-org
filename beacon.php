<?php

use Tsugi\Core\LTIX;
use Tsugi\Services\Admin\AdminService;

require_once __DIR__ . '/lib/beacon_store.php';

if ( ! isset($CFG) ) {
    if ( ! defined('COOKIE_SESSION') ) define('COOKIE_SESSION', true);
    require_once __DIR__ . '/tsugi/config.php';
}
if ( session_status() !== PHP_SESSION_ACTIVE ) {
    LTIX::session_start();
}

header('Cache-Control: private, no-store');
header('X-Robots-Tag: noindex');

$unlocked = AdminService::isAdmin();
if ( ! $unlocked ) http_response_code(403);

$OUTPUT->header();
require_once __DIR__ . '/nav.php';

echo '<div id="container" class="container">'."\n";
echo '<h1>Upgrade reports</h1>'."\n";

if ( ! $unlocked ) {
    $adminUrl = htmlspecialchars(rtrim((string) $CFG->wwwroot, '/') . '/admin/', ENT_QUOTES, 'UTF-8');
    echo '<p>These reports are for the maintainer. Unlock the admin console, then open this page again.</p>'."\n";
    echo '<p><a href="' . $adminUrl . '">Admin console</a></p>'."\n";
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}

$view = (isset($_GET['view']) && $_GET['view'] === 'wwwroot') ? 'wwwroot' : 'installs';
$showLocal = isset($_GET['show_local']) && $_GET['show_local'] === '1';
$sort = (isset($_GET['sort']) && $_GET['sort'] === 'created') ? 'created' : 'updated';
$wantId = isset($_GET['id']) ? (string) $_GET['id'] : '';

echo '<p>';
echo '<a href="' . beacon_page_qs('installs', $showLocal, $sort) . '">By install</a>';
echo ' · <a href="' . beacon_page_qs('wwwroot', $showLocal, $sort) . '">By wwwroot</a>';
if ( $showLocal ) {
    echo ' · <a href="' . beacon_page_qs($view, false, $sort) . '">Hide local</a>';
} else {
    echo ' · <a href="' . beacon_page_qs($view, true, $sort) . '">Show local</a>';
}
echo ' · ';
if ( $sort === 'updated' ) {
    echo '<strong>Updated</strong>';
} else {
    echo '<a href="' . beacon_page_qs($view, $showLocal, 'updated') . '">Updated</a>';
}
echo ' · ';
if ( $sort === 'created' ) {
    echo '<strong>Created</strong>';
} else {
    echo '<a href="' . beacon_page_qs($view, $showLocal, 'created') . '">Created</a>';
}
echo "</p>\n";
if ( ! $showLocal ) {
    echo '<p>Local installs stay in the database and are left out of this list. A local wwwroot uses localhost, 127.0.0.1, ::1, a host that starts with local., or a host that ends with .local or .localhost.</p>'."\n";
}

try {
    $pdo = beacon_open();
} catch ( Throwable $e ) {
    error_log('tsugi beacon admin open failed');
    echo '<p>The upgrade report database could not be opened.</p>'."\n";
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}

if ( $wantId !== '' ) {
    if ( ! preg_match('/^[0-9a-f]{64}$/', $wantId) ) {
        http_response_code(404);
        echo '<p>Unknown install.</p>'."\n";
    } else {
        $one = beacon_one($pdo, $wantId);
        if ( ! $one ) {
            http_response_code(404);
            echo '<p>Unknown install.</p>'."\n";
        } else {
            beacon_page_detail($one, beacon_history_for($pdo, $wantId));
        }
    }
    echo '<p><a href="' . beacon_page_qs($view, $showLocal, $sort) . '">Back to the list</a></p>'."\n";
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}

$rows = beacon_list($pdo, $showLocal, $sort);
if ( count($rows) < 1 ) {
    echo '<p>No upgrade reports yet.</p>'."\n";
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}

if ( $view === 'wwwroot' ) {
    $groups = array();
    foreach ( $rows as $row ) {
        $groups[$row['wwwroot']][] = $row;
    }
    foreach ( $groups as $wwwroot => $group ) {
        echo '<h2>' . beacon_page_h($wwwroot) . '</h2>'."\n";
        echo '<p>' . count($group) . ' install' . (count($group) === 1 ? '' : 's') . ' share this wwwroot.</p>'."\n";
        beacon_page_table($group, $view, $showLocal, $sort);
    }
} else {
    beacon_page_table($rows, $view, $showLocal, $sort);
}

echo "</div>\n";
$OUTPUT->footer();

function beacon_page_qs($view, $showLocal, $sort = 'updated') {
    $q = array();
    if ( $view === 'wwwroot' ) $q['view'] = 'wwwroot';
    if ( $showLocal ) $q['show_local'] = '1';
    if ( $sort === 'created' ) $q['sort'] = 'created';
    $built = http_build_query($q);
    return $built === '' ? '?' : ('?' . $built);
}

function beacon_page_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function beacon_page_table(array $rows, $view, $showLocal, $sort) {
    $updatedHref = beacon_page_h(beacon_page_qs($view, $showLocal, 'updated'));
    $createdHref = beacon_page_h(beacon_page_qs($view, $showLocal, 'created'));
    echo '<div style="overflow-x:auto">'."\n";
    echo '<table class="table table-striped">'."\n";
    echo '<thead><tr>';
    echo '<th>wwwroot</th><th>dirroot</th><th>Tsugi</th><th>software_version</th><th>recorded_overall</th>';
    echo '<th>Upgrades</th><th>Last report</th>';
    echo '<th><a href="' . $updatedHref . '">Updated</a></th>';
    echo '<th><a href="' . $createdHref . '">Created</a></th>';
    echo '<th>Git</th><th>Owner</th><th>Schemas</th>';
    echo "</tr></thead>\n<tbody>\n";
    foreach ( $rows as $row ) {
        $href = '?id=' . rawurlencode($row['id']);
        echo '<tr>';
        echo '<td><a href="' . beacon_page_h($href) . '">' . beacon_page_h($row['wwwroot']) . '</a>';
        if ( (int) $row['local'] === 1 ) echo ' <span>(local)</span>';
        echo '</td>';
        echo '<td>' . beacon_page_h($row['dirroot']) . '</td>';
        echo '<td>' . beacon_page_h($row['tsugi_version']) . '</td>';
        echo '<td>' . beacon_page_h($row['software_version']) . '</td>';
        echo '<td>' . beacon_page_h($row['recorded_overall']) . '</td>';
        echo '<td>' . (int) $row['upgrade_count'] . '</td>';
        echo '<td>' . beacon_page_h($row['reported_at']) . '</td>';
        echo '<td>' . beacon_page_h($row['updated_at']) . '</td>';
        echo '<td>' . beacon_page_h($row['created_at']) . '</td>';
        echo '<td>' . beacon_page_git($row) . '</td>';
        echo '<td>' . beacon_page_h($row['ownername']);
        if ( is_string($row['owneremail']) && $row['owneremail'] !== '' ) {
            echo '<br/>' . beacon_page_h($row['owneremail']);
        }
        echo '</td>';
        echo '<td>' . beacon_page_schemas($row['schemas_json']) . '</td>';
        echo "</tr>\n";
    }
    echo "</tbody></table></div>\n";
}

function beacon_page_git(array $row) {
    $parts = array();
    if ( is_string($row['git_commit']) && $row['git_commit'] !== '' ) $parts[] = beacon_page_h($row['git_commit']);
    if ( is_string($row['git_branch']) && $row['git_branch'] !== '' ) $parts[] = beacon_page_h($row['git_branch']);
    if ( count($parts) < 1 ) return '';
    return implode(' ', $parts);
}

function beacon_page_schemas($json) {
    $decoded = json_decode((string) $json, true);
    if ( ! is_array($decoded) ) return '';
    $n = count($decoded);
    if ( $n < 1 ) return '0';
    $html = '<details><summary>' . $n . '</summary><ul>';
    foreach ( $decoded as $path => $version ) {
        $html .= '<li>' . beacon_page_h($path) . ' ' . beacon_page_h($version) . '</li>';
    }
    $html .= '</ul></details>';
    return $html;
}

function beacon_page_detail(array $row, array $history) {
    echo '<p><a href="' . beacon_page_h('?id=' . $row['id']) . '">' . beacon_page_h($row['wwwroot']) . '</a>';
    if ( (int) $row['local'] === 1 ) echo ' (local, hidden from the default list)';
    echo '</p>'."\n";
    echo '<table class="table">'."\n";
    $fields = array(
        'dirroot' => 'dirroot',
        'apphome' => 'apphome',
        'servicename' => 'service',
        'ownername' => 'owner',
        'owneremail' => 'owner email',
        'tsugi_version' => 'Tsugi',
        'software_version' => 'database software_version',
        'recorded_overall' => 'recorded_overall',
        'highest_migration' => 'highest migration',
        'highest_migration_path' => 'highest migration path',
        'server_version' => 'database server',
        'driver' => 'driver',
        'prefix' => 'prefix',
        'php' => 'PHP',
        'sapi' => 'SAPI',
        'os' => 'OS',
        'os_release' => 'OS release',
        'hostname' => 'hostname',
        'timezone' => 'timezone',
        'server_software' => 'server software',
        'reported_at' => 'last report',
        'updated_at' => 'updated',
        'created_at' => 'created',
        'comment' => 'comment',
    );
    foreach ( $fields as $key => $label ) {
        $value = $row[$key];
        if ( $value === null || $value === '' ) continue;
        echo '<tr><th>' . beacon_page_h($label) . '</th><td>' . beacon_page_h($value) . '</td></tr>'."\n";
    }
    echo '<tr><th>upgrades</th><td>' . (int) $row['upgrade_count'] . '</td></tr>'."\n";
    echo '<tr><th>git</th><td>' . beacon_page_git($row) . '</td></tr>'."\n";
    echo '<tr><th>schemas</th><td>' . beacon_page_schemas($row['schemas_json']) . '</td></tr>'."\n";
    echo "</table>\n";

    echo '<h2>Upgrade history</h2>'."\n";
    if ( count($history) < 1 ) {
        echo '<p>No history rows.</p>'."\n";
        return;
    }
    echo '<table class="table table-striped">'."\n";
    echo '<thead><tr><th>Reported</th><th>Updated</th><th>Created</th><th>software_version</th><th>recorded_overall</th><th>Git</th><th>HTTP</th></tr></thead><tbody>'."\n";
    foreach ( $history as $item ) {
        echo '<tr>';
        echo '<td>' . beacon_page_h($item['reported_at']) . '</td>';
        echo '<td>' . beacon_page_h($item['updated_at']) . '</td>';
        echo '<td>' . beacon_page_h($item['created_at']) . '</td>';
        echo '<td>' . beacon_page_h($item['software_version']) . '</td>';
        echo '<td>' . beacon_page_h($item['recorded_overall']) . '</td>';
        echo '<td>' . beacon_page_git($item) . '</td>';
        echo '<td>' . beacon_page_h($item['http_status']) . '</td>';
        echo "</tr>\n";
    }
    echo "</tbody></table>\n";
}
