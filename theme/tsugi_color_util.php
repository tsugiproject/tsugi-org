<?php


require_once("Color.php");
require_once("rgbToHSL.php");

function fixRgb($r, $g=0, $b=0) {
    if ( is_array($r) ) return $r;
    if ( is_string($r) ) return Color::rgb($r);
    return [$r, $g, $b];
}

function findLMidPointForHue($r, $g=false, $b=false, $dark=false) {
    // echo("findLMidPoint $r $g $b\n");
    if ( ! $dark ) $dark = "#000000";
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
        $relblack = Color::relativeLuminance($dark, $hex);
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
    // echo("luminosityPair $difference $r $g $b\n");
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

function deriveTsugiColors($tsugi_dark) {
    $fromwhite = Color::relativeLuminance("#FFFFFF", $tsugi_dark);
    if ( $fromwhite < 8.0 ) {
        $mid = findLMidPointForHue($tsugi_dark);
    } else {
        $rgb = fixRgb($tsugi_dark);
        $mid = findLMidPointForHue($rgb[0], $rgb[1], $rgb[2], $tsugi_dark);
    }

    $outerpair = luminosityPair(20.0, $mid);
    $innerpair = luminosityPair(7.0, $mid);

    $dark_outer_hsl = rgbToHsl($outerpair[0]);
    $dark_inner_hsl = rgbToHsl($innerpair[0]);

    $light_inner_hsl = rgbToHsl($innerpair[1]);
    $light_outer_hsl = rgbToHsl($outerpair[1]);

    $tsugi_dark_hsl = rgbToHsl($tsugi_dark);

    $ddelta = $dark_inner_hsl[2] - $dark_outer_hsl[2];
    $ldelta = $light_outer_hsl[2] - $light_inner_hsl[2];

    $hue = $dark_outer_hsl[0];
    $sat_dark = $dark_outer_hsl[1];
    $sat_light = $light_outer_hsl[1];
    $lightness_dark = $dark_outer_hsl[2];
    $lightness_light = $light_outer_hsl[2];

    if ( $fromwhite < 8.0 ) {
        $tsugi_dark = Color::hex(hslToRgb($hue, $sat_dark, $lightness_dark + ($ddelta * 0.6)));
        $tsugi_dark_hsl = rgbToHsl($tsugi_dark);
    }

    $lightness_darker = ($tsugi_dark_hsl[2] + $dark_outer_hsl[2]) / 2.0;
    $lightness_dark_accent = ($tsugi_dark_hsl[2] + $dark_inner_hsl[2]) / 2.0;

    $tsuginames = array(
        "tsugi-dark-background" => $outerpair[0],
        "tsugi-dark-text" =>  Color::hex(hslToRgb($hue, $sat_dark*0.5, $lightness_darker)),
        "tsugi-dark-darker" => Color::hex(hslToRgb($hue, $sat_dark, $lightness_darker)),
        "tsugi-dark" =>  $tsugi_dark,
        "tsugi-dark-accent" => $innerpair[0],
        "tsugi-mid" => $mid,
        "tsugi-light-accent" => $innerpair[1],
        "tsugi-light" => Color::hex(hslToRgb($hue, $sat_light, $lightness_light - ($ldelta * 0.6))),
        "tsugi-light-lighter" => Color::hex(hslToRgb($hue, $sat_light, $lightness_light - ($ldelta * 0.3))),
        "tsugi-light-text" => Color::hex(hslToRgb($hue, $sat_light*0.5, $lightness_light - ($ldelta * 0.3))),
        "tsugi-light-background" => $outerpair[1],
    );

    return $tsuginames;
}

