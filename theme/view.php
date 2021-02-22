<?php

require_once("HSLuv.php");
require_once("tsugi_color_util.php");

$color = '';
if ( isset($_REQUEST['color']) ) {
    echo("<pre>\n");
    $color = $_REQUEST['color'];
    $mid = findLMidPointForHue($_REQUEST['color']);

    $pair = luminosityPair(10.0, $mid);
    echo("</pre>\n");
    echo("<p>\n");
    echo('<span style="color: '.$_REQUEST['color'].';">'.$_REQUEST['color'].'</span> ');
    echo('<span style="color: '.$pair[0].';">'.$pair[0].'</span> ');
    echo('<span style="color: '.$mid.';">'.$mid.'</span> ');
    echo('<span style="color: '.$pair[1].';">'.$pair[1].'</span> ');
    echo("</p>\n");
    echo("<p>\n");
    echo('<span style="padding: 5px; color: '.$pair[1].'; background-color: '.$pair[0].';">'.$pair[1].'</span> ');
    echo("</p>\n");
}
?>
<form>
<input type="color" name="color" value="<?= $color ?>">
<input type="submit">
</form>
