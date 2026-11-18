<?php

use \Tsugi\Core\LTIX;

// Same beacon path when this front controller runs without the rewrite
// (FallbackResource, or the built-in PHP server). Still before config and
// session, so the body is not logged and MySQL is not required.
$beaconPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if ( is_string($beaconPath) && preg_match('#/api/beacon/upgrade/?$#', $beaconPath) ) {
    require __DIR__ . '/api/beacon/upgrade.php';
    return;
}

define('COOKIE_SESSION', true);
require_once "tsugi/config.php";

$launch = LTIX::session_start();

// Make PHP paths pretty .../install => install.php
$router = new Tsugi\Util\FileRouter();
$file = $router->fileCheck();
if ( $file ) {
    require_once($file);
    return;
}

// Pull in the Koseu LMS (/lessons, /map, /badges ...)
$app = new \Koseu\Core\Application($launch);
$app['debug'] = true;

$app->run();
