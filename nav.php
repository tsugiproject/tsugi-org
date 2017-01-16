<?php
$OUTPUT->bodyStart();
$R = $CFG->apphome . '/';
$T = $CFG->wwwroot . '/';
$adminmenu = isset($_COOKIE['adminmenu']) && $_COOKIE['adminmenu'] == "true";
$set = new \Tsugi\UI\MenuSet();
$set->setHome('<img style="width:4em; padding: 4px; border-radius: 4px; background-color:white;" src="'. $CFG->staticroot . '/img/logos/tsugi-logo.png' .'">', $CFG->apphome."#index");
$set->addLeft('Documentation', $R .'#docs');
$set->addLeft('GitHub', $R.'docs/repos.php');
$set->addLeft('Install', $R .'docs/install.php');
if ( isset($CFG->lessons) ) {
    $set->addLeft('Tutorials', $T.'lessons.php');
}
$set->addLeft('Videos', 'https://www.youtube.com/playlist?list=PLlRFEj9H3Oj5WZUjVjTJVBN18ozYSWMhw');
$set->addLeft('Discuss', 'https://groups.google.com/a/apereo.org/forum/#!forum/tsugi-dev');

if ( isset($_SESSION['id']) ) {
    $submenu = new \Tsugi\UI\Menu();
    $submenu->addLink('Profile', $T.'profile.php');
    if ( isset($CFG->google_map_api_key) && $adminmenu ) {
        $submenu->addLink('Map', $T.'map.php');
    }
    if ( isset($_SESSION['id']) ) {
	if ( isset($CFG->disqushost) ) $set->addLeft('Discuss', $T.'discuss.php');
	else if ( isset($CFG->disquschannel) ) $set->addLeft('Discuss', $CFG->disquschannel);
	$submenu->addLink('Assignments', $T.'assignments.php');
    }
    if ( $CFG->DEVELOPER ) {
        $submenu->addLink('Test LTI Tools', $T . 'dev.php');
    }
    if ( $CFG->providekeys ) {
        $submenu->addLink('Use this Service', $T . 'admin/key/index.php');
    }
    if ( isset($_COOKIE['adminmenu']) && $_COOKIE['adminmenu'] == "true" ) {
        $submenu->addLink('Administer', $T . 'admin/');
    }
    $submenu->addLink('Logout', $T.'logout.php');
    if ( isset($_SESSION['avatar']) ) {
        $set->addRight('<img src="'.$_SESSION['avatar'].'" style="height: 2em;"/>', $submenu);
    } else {
        $set->addRight(htmlentities($_SESSION['displayname']), $submenu);
    }
} else {
    $set->addRight('Login', $T.'login.php');
}

$set->addRight('About', $R.'#about');

// Set the topNav for the session
$OUTPUT->topNavSession($set);

$OUTPUT->topNav();
$OUTPUT->flashMessages();

