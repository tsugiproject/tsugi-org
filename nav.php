<?php
$OUTPUT->bodyStart();
$R = $CFG->apphome . '/';
$T = $CFG->wwwroot . '/';
$adminmenu = isset($_COOKIE['adminmenu']) && $_COOKIE['adminmenu'] == "true";
$set = new \Tsugi\UI\MenuSet();
// $set->setHome('<img style="top: -5px; width:4em; padding: 4px; border-radius: 4px; background-color:white;" src="'. $CFG->staticroot . '/img/logos/tsugi-logo.png' .'">', $CFG->apphome."#index");
$set->setHome('TSUGI', $CFG->apphome."#index");
$set->addLeft('Documentation', $R .'#docs');
if ( isset($CFG->lessons) ) {
    $set->addLeft('Tutorials', $R.'lessons');
}
$set->addLeft('GitHub', $R.'docs/repos');
$set->addLeft('Discuss', 'https://developers.tsugi.org');

if ( isset($_SESSION['id']) ) {
    $submenu = new \Tsugi\UI\Menu();
    $submenu->addLink('Profile', $R.'profile');
    if ( isset($CFG->google_map_api_key) && $adminmenu ) {
        $submenu->addLink('Map', $R.'map');
    }
    if ( isset($_SESSION['id']) ) {
	$submenu->addLink('Assignments', $R.'assignments');
    }
    $submenu->addLink('Tsugi App Store', 'https://www.tsugicloud.org');
    if ( $CFG->providekeys ) {
        $submenu->addLink('Use this Service', $T . 'settings');
    }
    if ( isset($_COOKIE['adminmenu']) && $_COOKIE['adminmenu'] == "true" ) {
        $submenu->addLink('Administer', $T . 'admin/');
    }
    $submenu->addLink('Logout', $R.'logout');
    if ( isset($_SESSION['avatar']) ) {
        $set->addRight('<img src="'.$_SESSION['avatar'].'" style="height: 2em;"/>', $submenu);
    } else {
        $set->addRight(htmlentities($_SESSION['displayname']), $submenu);
    }
} else {
    $set->addRight('Login', $R.'login');
}

$set->addRight('About', $R.'#about');

// Set the topNav for the session
$OUTPUT->topNavSession($set);

$OUTPUT->topNav();
$OUTPUT->flashMessages();

