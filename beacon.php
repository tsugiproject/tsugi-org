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

$view = (isset($_GET['view']) && $_GET['view'] === 'wwwroot') ? 'wwwroot' : 'installs';
$showLocal = isset($_GET['show_local']) && $_GET['show_local'] === '1';
$sortColumns = beacon_list_columns();
$sort = (isset($_GET['sort']) && isset($sortColumns[$_GET['sort']])) ? $_GET['sort'] : 'updated_at';
$dir = (isset($_GET['dir']) && $_GET['dir'] === 'asc') ? 'asc' : 'desc';
$search = beacon_search_needle(isset($_GET['q']) ? $_GET['q'] : '');
$wantId = isset($_GET['id']) ? (string) $_GET['id'] : '';
$deleteError = '';

if ( $unlocked && (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && isset($_POST['delete_id']) ) {
    try {
        $deletePdo = beacon_open();
        if ( ! \Tsugi\Controllers\Tool::csrfOk() ) {
            $deleteError = 'The delete was rejected. Reload the page and try again.';
        } else {
            $deleteId = (string) $_POST['delete_id'];
            if ( preg_match('/^[0-9a-f]{64}$/', $deleteId) ) beacon_delete($deletePdo, $deleteId);
            header('Location: ' . beacon_page_qs($view, $showLocal, $sort, $dir, $search), true, 303);
            exit;
        }
    } catch ( Throwable $e ) {
        error_log('tsugi beacon delete failed');
        $deleteError = 'The install could not be deleted.';
    }
}

$OUTPUT->header();
require_once __DIR__ . '/nav.php';

echo '<div id="container" class="container">'."\n";
echo '<h1>Beacon Reports</h1>'."\n";
if ( $deleteError !== '' ) echo '<p>' . beacon_page_h($deleteError) . '</p>'."\n";

if ( ! $unlocked ) {
    $adminUrl = htmlspecialchars(rtrim((string) $CFG->wwwroot, '/') . '/admin/', ENT_QUOTES, 'UTF-8');
    echo '<p>These reports are for the maintainer. Unlock the admin console, then open this page again.</p>'."\n";
    echo '<p><a href="' . $adminUrl . '">Admin console</a></p>'."\n";
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}

echo '<style>
.beacon-new-note { color: #157347; font-weight: 600; }
tr.beacon-new > td { background-color: #e7f6ea !important; }
</style>'."\n";
echo '<p>';
echo '<a href="' . beacon_page_qs('installs', $showLocal, $sort, $dir, $search) . '">By install</a>';
echo ' · <a href="' . beacon_page_qs('wwwroot', $showLocal, $sort, $dir, $search) . '">By host</a>';
if ( $showLocal ) {
    echo ' · <a href="' . beacon_page_qs($view, false, $sort, $dir, $search) . '">Hide local</a>';
} else {
    echo ' · <a href="' . beacon_page_qs($view, true, $sort, $dir, $search) . '">Show local</a>';
}
echo "</p>\n";
if ( ! $showLocal ) {
    echo '<p>Local installs stay in the database and are left out of this list. A local host is localhost, 127.0.0.1, ::1, a name that starts with local., or a name that ends with .local or .localhost.</p>'."\n";
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
            $history = beacon_history_for($pdo, $wantId);
            $places = beacon_ip_places($pdo, beacon_page_ips($one, $history));
            $rows = beacon_list($pdo, $showLocal, $sort, $dir, $search);
            beacon_page_nav($wantId, $rows, $view, $showLocal, $sort, $dir, $search);
            beacon_page_detail($one, $history, $places);
            beacon_page_delete_form($wantId, $view, $showLocal, $sort, $dir, $search);
            beacon_page_nav($wantId, $rows, $view, $showLocal, $sort, $dir, $search);
        }
    }
    if ( ! isset($rows) ) {
        echo '<p><a href="' . beacon_page_qs($view, $showLocal, $sort, $dir, $search) . '">Back to the list</a></p>'."\n";
    }
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}

echo '<form method="get" action="">'."\n";
if ( $view === 'wwwroot' ) echo '<input type="hidden" name="view" value="wwwroot">'."\n";
if ( $showLocal ) echo '<input type="hidden" name="show_local" value="1">'."\n";
if ( $sort !== 'updated_at' ) echo '<input type="hidden" name="sort" value="' . beacon_page_h($sort) . '">'."\n";
if ( $dir !== 'desc' ) echo '<input type="hidden" name="dir" value="asc">'."\n";
echo '<label>Search <input type="search" name="q" value="' . beacon_page_h($search) . '" size="40"></label> '."\n";
echo '<button type="submit">Search</button>'."\n";
if ( $search !== '' ) {
    echo ' <a href="' . beacon_page_qs($view, $showLocal, $sort, $dir, '') . '">Clear</a>'."\n";
}
echo "</form>\n";

$rows = beacon_list($pdo, $showLocal, $sort, $dir, $search);
if ( count($rows) < 1 ) {
    if ( $search !== '' ) {
        echo '<p>No matches.</p>'."\n";
    } else {
        echo '<p>No upgrade reports yet.</p>'."\n";
    }
    echo "</div>\n";
    $OUTPUT->footer();
    return;
}
if ( $search !== '' ) {
    echo '<p>' . count($rows) . ' match' . (count($rows) === 1 ? '' : 'es') . '.</p>'."\n";
}
$newCount = 0;
foreach ( $rows as $row ) {
    if ( beacon_page_is_new($row) ) $newCount++;
}
if ( $newCount > 0 ) {
    echo '<p class="beacon-new-note">' . $newCount . ' new</p>'."\n";
}

if ( $view === 'wwwroot' ) {
    $groups = array();
    foreach ( $rows as $row ) {
        $groups[$row['wwwroot']][] = $row;
    }
    foreach ( $groups as $wwwroot => $group ) {
        echo '<h2>' . beacon_page_h($wwwroot) . '</h2>'."\n";
        echo '<p>' . count($group) . ' install' . (count($group) === 1 ? '' : 's') . ' share this host.</p>'."\n";
        beacon_page_table($group, $view, $showLocal, $sort, $dir, $search);
    }
} else {
    beacon_page_table($rows, $view, $showLocal, $sort, $dir, $search);
}

echo "</div>\n";
$OUTPUT->footer();

function beacon_page_id_href($id, $view, $showLocal, $sort, $dir, $search) {
    $href = beacon_page_qs($view, $showLocal, $sort, $dir, $search);
    $href .= ($href === '?') ? 'id=' : '&id=';
    return $href . rawurlencode($id);
}

function beacon_page_sequence(array $rows, $view) {
    if ( $view !== 'wwwroot' ) {
        $ids = array();
        foreach ( $rows as $row ) $ids[] = $row['id'];
        return $ids;
    }
    $groups = array();
    foreach ( $rows as $row ) {
        $groups[$row['wwwroot']][] = $row['id'];
    }
    $ids = array();
    foreach ( $groups as $group ) {
        foreach ( $group as $id ) $ids[] = $id;
    }
    return $ids;
}

function beacon_page_nav($id, array $rows, $view, $showLocal, $sort, $dir, $search) {
    $ids = beacon_page_sequence($rows, $view);
    $index = array_search($id, $ids, true);
    $parts = array();
    if ( $index !== false && $index > 0 ) {
        $href = beacon_page_id_href($ids[$index - 1], $view, $showLocal, $sort, $dir, $search);
        $parts[] = '<a href="' . beacon_page_h($href) . '">Back</a>';
    }
    if ( $index !== false ) {
        $parts[] = ($index + 1) . ' of ' . count($ids);
    }
    if ( $index !== false && $index < count($ids) - 1 ) {
        $href = beacon_page_id_href($ids[$index + 1], $view, $showLocal, $sort, $dir, $search);
        $parts[] = '<a href="' . beacon_page_h($href) . '">Next</a>';
    }
    $parts[] = '<a href="' . beacon_page_h(beacon_page_qs($view, $showLocal, $sort, $dir, $search)) . '">Back to the list</a>';
    echo '<p>' . implode(' · ', $parts) . '</p>'."\n";
}

function beacon_page_qs($view, $showLocal, $sort = 'updated_at', $dir = 'desc', $search = '') {
    $q = array();
    if ( $view === 'wwwroot' ) $q['view'] = 'wwwroot';
    if ( $showLocal ) $q['show_local'] = '1';
    if ( $sort !== 'updated_at' ) $q['sort'] = $sort;
    if ( $dir === 'asc' ) $q['dir'] = 'asc';
    if ( $search !== '' ) $q['q'] = $search;
    $built = http_build_query($q);
    return $built === '' ? '?' : ('?' . $built);
}

function beacon_page_header_link($column, $label, $view, $showLocal, $sort, $dir, $search) {
    $nextDir = beacon_sort_default_dir($column);
    $marker = '';
    if ( $column === $sort ) {
        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
        $marker = $dir === 'asc' ? ' ↑' : ' ↓';
    }
    $href = beacon_page_qs($view, $showLocal, $column, $nextDir, $search);
    return '<a href="' . beacon_page_h($href) . '">' . beacon_page_h($label . $marker) . '</a>';
}

function beacon_sort_default_dir($column) {
    $kind = beacon_list_columns()[$column] ?? 'text';
    if ( $kind === 'number' || $column === 'updated_at' || $column === 'created_at' ) {
        return 'desc';
    }
    return 'asc';
}

function beacon_page_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function beacon_page_upgrades(array $row) {
    $count = (int) ($row['upgrade_count'] ?? 0);
    return $count > 0 ? $count - 1 : 0;
}

function beacon_page_is_new(array $row) {
    return beacon_page_upgrades($row) === 0;
}

function beacon_page_table(array $rows, $view, $showLocal, $sort, $dir, $search) {
    $headers = array(
        'wwwroot' => 'Host',
        'install_count' => 'Installs',
        'servicename' => 'Service',
        'tsugi_version' => 'Tsugi',
        'software_version' => 'Database',
        'php' => 'PHP',
        'server_version' => 'MySQL',
        'upgrade_count' => 'Upgrades',
        'updated_at' => 'Updated',
        'created_at' => 'Created',
        'client_ip' => 'IP',
        'git' => 'Git',
        'owner' => 'Owner',
    );
    echo '<div style="overflow-x:auto">'."\n";
    echo '<table class="table table-striped">'."\n";
    echo '<thead><tr>';
    foreach ( $headers as $column => $label ) {
        echo '<th>' . beacon_page_header_link($column, $label, $view, $showLocal, $sort, $dir, $search) . '</th>';
    }
    echo "<th></th>";
    echo "</tr></thead>\n<tbody>\n";
    foreach ( $rows as $row ) {
        $href = beacon_page_id_href($row['id'], $view, $showLocal, $sort, $dir, $search);
        $upgrades = beacon_page_upgrades($row);
        echo beacon_page_is_new($row) ? '<tr class="beacon-new">' : '<tr>';
        echo '<td><a href="' . beacon_page_h($href) . '">' . beacon_page_h($row['wwwroot']) . '</a>';
        if ( (int) $row['local'] === 1 ) echo ' <span>(local)</span>';
        echo '</td>';
        echo '<td>' . (int) ($row['install_count'] ?? 0) . '</td>';
        echo '<td>' . beacon_page_h($row['servicename']) . '</td>';
        echo '<td>' . beacon_page_h($row['tsugi_version']) . '</td>';
        echo '<td>' . beacon_page_h($row['software_version']) . '</td>';
        echo '<td>' . beacon_page_h($row['php']) . '</td>';
        echo '<td>' . beacon_page_h($row['server_version']) . '</td>';
        echo '<td>' . $upgrades;
        if ( $upgrades === 0 ) echo ' <span class="beacon-new-note">new</span>';
        echo '</td>';
        echo '<td>' . beacon_page_h($row['updated_at']) . '</td>';
        echo '<td>' . beacon_page_h($row['created_at']) . '</td>';
        echo '<td>' . beacon_page_client($row) . '</td>';
        echo '<td>' . beacon_page_git($row) . '</td>';
        echo '<td>' . beacon_page_h($row['ownername']);
        if ( is_string($row['owneremail']) && $row['owneremail'] !== '' ) {
            echo '<br/>' . beacon_page_h($row['owneremail']);
        }
        echo '</td>';
        echo '<td>';
        beacon_page_delete_form($row['id'], $view, $showLocal, $sort, $dir, $search);
        echo '</td>';
        echo "</tr>\n";
    }
    echo "</tbody></table></div>\n";
}

function beacon_page_delete_form($id, $view, $showLocal, $sort, $dir, $search) {
    $action = beacon_page_h(beacon_page_qs($view, $showLocal, $sort, $dir, $search));
    echo '<form method="post" action="' . $action . '" onsubmit="return confirm(\'Delete this install and its upgrade history?\');">'."\n";
    echo \Tsugi\Controllers\Tool::csrfField();
    echo '<input type="hidden" name="delete_id" value="' . beacon_page_h($id) . '">'."\n";
    echo '<button type="submit">Delete</button>'."\n";
    echo "</form>\n";
}

function beacon_page_ips(array $row, array $history) {
    $ips = array();
    if ( is_string($row['client_ip'] ?? null) && $row['client_ip'] !== '' ) $ips[] = $row['client_ip'];
    foreach ( $history as $item ) {
        if ( is_string($item['client_ip'] ?? null) && $item['client_ip'] !== '' ) $ips[] = $item['client_ip'];
    }
    return $ips;
}

function beacon_page_place($place) {
    if ( ! is_array($place) ) return '';
    $city = is_string($place['city'] ?? null) ? $place['city'] : '';
    $country = is_string($place['country_code'] ?? null) ? $place['country_code'] : '';
    if ( $city !== '' && $country !== '' ) return $city . ', ' . $country;
    if ( $city !== '' ) return $city;
    return $country;
}

function beacon_page_client(array $row, $place = '') {
    $html = beacon_page_h($row['client_ip'] ?? '');
    if ( is_string($row['client_host'] ?? null) && $row['client_host'] !== '' ) {
        $html .= '<br/>' . beacon_page_h($row['client_host']);
    }
    if ( $place !== '' ) $html .= '<br/>' . beacon_page_h($place);
    return $html;
}

function beacon_page_git(array $row) {
    $parts = array();
    if ( is_string($row['git_commit']) && $row['git_commit'] !== '' ) $parts[] = beacon_page_h($row['git_commit']);
    if ( is_string($row['git_branch']) && $row['git_branch'] !== '' ) $parts[] = beacon_page_h($row['git_branch']);
    if ( count($parts) < 1 ) return '';
    return implode(' ', $parts);
}

function beacon_page_detail(array $row, array $history, array $places = array()) {
    echo '<p><a href="' . beacon_page_h('?id=' . $row['id']) . '">' . beacon_page_h($row['wwwroot']) . '</a>';
    if ( (int) $row['local'] === 1 ) echo ' (local, hidden from the default list)';
    echo '</p>'."\n";
    echo '<table class="table">'."\n";
    $fields = array(
        'wwwroot' => 'host',
        'servicename' => 'service',
        'ownername' => 'owner',
        'owneremail' => 'owner email',
        'tsugi_version' => 'Tsugi',
        'software_version' => 'database version',
        'server_version' => 'MySQL',
        'php' => 'PHP',
        'reported_at' => 'last report',
        'timezone' => 'timezone',
        'updated_at' => 'updated',
        'created_at' => 'created',
        'client_ip' => 'client IP',
        'client_host' => 'client host',
        'comment' => 'comment',
    );
    foreach ( $fields as $key => $label ) {
        $value = $row[$key];
        if ( $value === null || $value === '' ) continue;
        echo '<tr><th>' . beacon_page_h($label) . '</th><td>' . beacon_page_h($value) . '</td></tr>'."\n";
        if ( $key === 'client_ip' ) {
            $where = beacon_page_place($places[$value] ?? null);
            if ( $where !== '' ) {
                echo '<tr><th>location</th><td>' . beacon_page_h($where) . '</td></tr>'."\n";
            }
        }
    }
    $upgrades = beacon_page_upgrades($row);
    echo '<tr><th>upgrades</th><td>' . $upgrades;
    if ( $upgrades === 0 ) echo ' <span class="beacon-new-note">new</span>';
    echo '</td></tr>'."\n";
    echo '<tr><th>git</th><td>' . beacon_page_git($row) . '</td></tr>'."\n";
    echo "</table>\n";

    echo '<h2>Beacon History</h2>'."\n";
    if ( count($history) < 1 ) {
        echo '<p>No history rows.</p>'."\n";
        return;
    }
    echo '<table class="table table-striped">'."\n";
    echo '<thead><tr><th>Reported</th><th>Updated</th><th>PHP</th><th>MySQL</th><th>IP</th><th>Database</th><th>Git</th><th>HTTP</th></tr></thead><tbody>'."\n";
    foreach ( $history as $item ) {
        echo '<tr>';
        echo '<td>' . beacon_page_h($item['reported_at']) . '</td>';
        echo '<td>' . beacon_page_h($item['updated_at']) . '</td>';
        echo '<td>' . beacon_page_h($item['php']) . '</td>';
        echo '<td>' . beacon_page_h($item['server_version']) . '</td>';
        echo '<td>' . beacon_page_client($item, beacon_page_place($places[$item['client_ip'] ?? ''] ?? null)) . '</td>';
        echo '<td>' . beacon_page_h($item['software_version']) . '</td>';
        echo '<td>' . beacon_page_git($item) . '</td>';
        echo '<td>' . beacon_page_h($item['http_status']) . '</td>';
        echo "</tr>\n";
        $comment = is_string($item['comment'] ?? null) ? $item['comment'] : '';
        if ( $comment !== '' ) {
            echo '<tr><td colspan="8" style="border-top:0;padding-top:0;white-space:pre-wrap;">' . beacon_page_h($comment) . '</td></tr>'."\n";
        }
    }
    echo "</tbody></table>\n";
}
