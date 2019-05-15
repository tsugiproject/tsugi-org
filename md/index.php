<?php

if ( ! defined('COOKIE_SESSION') ) define('COOKIE_SESSION', true);

require_once "../tsugi/config.php";
require_once "Parsedown.php";

require_once "../top.php";
require_once "../nav.php";

if ( ! function_exists('endsWith') ) {
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}
}

$url = $_SERVER['REQUEST_URI'];

$pieces = explode('/',$url);

$file = false;
$contents = false;
if ( $pieces >= 2 ) {
   $file = $pieces[count($pieces)-1];
   if ( ! endsWith($file, '.md') ) $file = false;
   if ( ! $file || ! file_exists($file) ) $file = false;
}

if ( $file !== false ) {
    $contents = file_get_contents($file);
}

$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav();

if ( $contents != false ) {
    $Parsedown = new Parsedown();
    echo $Parsedown->text($contents);
} else {
?>
<ul>
<li><a href="ADVANTAGE.md">Using Tsugi with LTI Advantage</a></li>
<li><a href="CHECKOUT_ALL.md">Checking Code Out</a></li>
<li><a href="CODING.md">Coding Style</a></li>
<li><a href="DEVELOP.md">How to Develop</a></li>
<li><a href="GITHUB.md">How to use GitHub</a></li>
<li><a href="I18N.md">iInternationalizing a Tsugi Tool</a></li>
<li><a href="INSTALL.md">Installing Tsugi</a></li>
<li><a href="LAUNCHING.md">Launching Tsugi</a></li>
<li><a href="LOGIN.md">Setting up Login via Google</a></li>
<li><a href="PHPDOC.md">How to use PHP Doc</a></li>
<li><a href="README_CANVAS_APP_STORE.md">Using Tsugi as an App Store in Canvas</a></li>
<li><a href="README.md">README</a></li>
</ul>
<?php
}
$OUTPUT->footer();
