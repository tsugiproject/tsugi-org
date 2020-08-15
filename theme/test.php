<?php

require_once("Color.php");
require_once("rgbToHSL.php");
require_once("HSLuv.php");
require_once("tsugi_color_util.php");

$rel = Color::relativeLuminance("#000000", "#FFFFFF");
echo("$rel \n");
$lasthex = false;
for($h=0;$h<360;$h=$h+30) {
    $l = 0.5;
    $s = 1.0;
    $rgb = hslToRgb( $h, $s, $l );
    $hex = Color::hex($rgb);
    $lum = Color::luminance ($rgb);
    $rel = 0;
    if ( $lasthex ) {
        $rel = Color::relativeLuminance($hex, $lasthex);
    }
    echo("$h, $s, $l, $hex, $lum, $lasthex, $rel\n");
    $lasthex = $hex;
}

$white = "#FFFFF";
for($b=0; $b<=255; $b=$b+16) {
    $r=0;
    $g = 0;
    $rgb = [ $r, $g, $b];
    $hex = Color::hex($rgb);
    $lum = Color::luminance ($rgb);
    $rel = Color::relativeLuminance($hex, $white);
    echo("$hex, $lum, $white, $rel\n");
}

$rgb = \HSLuv\HSLuv::fromHex("#000000");
$luv = \HSLuv\HSLuv::rgbToHpluv($rgb);
print_r($luv);
$rgb = \HSLuv\HSLuv::fromHex("#FFFFFF");
$luv = \HSLuv\HSLuv::rgbToHpluv($rgb);
print_r($luv);
$rgb = \HSLuv\HSLuv::fromHex("#0000FF");
$luv = \HSLuv\HSLuv::rgbToHpluv($rgb);
print_r($luv);

$mid = findLMidPoint("#0000FF");

$pair = luminosityPair(7.0, $mid);
print_r($pair);
