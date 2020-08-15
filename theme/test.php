<?php

require_once("Color.php");
require_once("rgbToHSL.php");

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
