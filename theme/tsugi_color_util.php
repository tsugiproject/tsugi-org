<?php


require_once("Color.php");
require_once("rgbToHSL.php");

function fixRgb($r, $g, $b) {
    if ( is_array($r) ) return $r;
    if ( is_string($r) ) return Color::rgb($r);
    return [$r, $g, $b];
}
function findLMidPoint($r, $g=false, $b=false) {
    echo("findLMidPoint $r $g $b\n");
    $rgb = fixRgb($r, $g, $b);
    // print_r($rgb);
    $hsl = rgbToHsl($rgb[0], $rgb[1], $rgb[2] );
    // print_r($hsl);
    $h = $hsl[0];
    $s = $hsl[1];
    $l = $hsl[2];
    $lasttot = 100;  // > 21
    for($l = 0.0; $l <= 1.0; $l += 0.01 ) {
        $rgb = hslToRgb( $h, $s, $l );
        // print_r($rgb);
        $hex = Color::hex($rgb);
        // Come down from white
        $relblack = Color::relativeLuminance("#000000", $hex);
        $relwhite = Color::relativeLuminance("#FFFFFF", $hex);
        $reltot = $relblack + $relwhite;
        // echo("#000000 $h $s $l $hex $relblack $relwhite $reltot\n");
        if ( $reltot > $lasttot ) return ($hex);
        $lasttot = $reltot;
        // if ( $relblack > 10.5 ) { return $hex; }
    }
    return $hex;

}

function luminosityPair($difference, $r, $g=false, $b=false) {
    echo("luminosityPair $difference $r $g $b\n");
    $rgb = fixRgb($r, $g, $b);
    // print_r($rgb);
    $hex = Color::hex($rgb);
    $relblack = Color::relativeLuminance("#000000", $hex);
    $relwhite = Color::relativeLuminance("#FFFFFF", $hex);
    // print("w $relwhite b $relblack\n");
    $hsl = rgbToHsl($rgb[0], $rgb[1], $rgb[2] );
    // print_r($hsl);
    $h = $hsl[0];
    $s = $hsl[1];
    $l = $hsl[2];
    $updist = 1.0 - $l;
    $downdist = $l;
    $increment = 0.01;
    for($d = 0.0; $d <= 1.0; $d += 0.01 ) {
        $downl =  $l - ($d * $downdist);
        $upl =  $l + ($d * $updist);
        $downrgb = hslToRgb( $h, $s, $downl );
        $uprgb = hslToRgb( $h, $s, $upl );
        // print_r($rgb);
        $uphex = Color::hex($uprgb);
        $downhex = Color::hex($downrgb);
        // Go up from black
        $rel = Color::relativeLuminance($downhex, $uphex);
        // echo("$downhex $uphex $rel\n");
        if ( $rel > $difference ) { return [$downhex, $uphex]; }
    }
}

