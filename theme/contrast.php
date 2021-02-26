<?php

require_once("../tsugi/vendor/tsugi/lib/src/Util/Color.php");

use \Tsugi\Util\Color;

$color = '#000000';
if ( isset($_REQUEST['color']) ) {
    $color = $_REQUEST['color'];
}
?>
<h1>Tsugi Contrast Checker</h1>
<p>Why do we need a contrast checker when 100's already exist?  This one uses the input type "color" 
so you can use an eye dropper.
</p>
<form>
Choose color: <input type="color" name="color" value="<?= $color ?>"><br/>
<input type="submit" value="Check">
</form>
<hr/>
<?php 
echo("<pre>\n");
$fromblack = Color::relativeLuminance("#000000", $color);
$fromwhite = Color::relativeLuminance("#FFFFFF", $color);
echo("Selected color: $color \n");
echo("Contrast from white: $fromwhite \n");
echo("Contrast from black: $fromblack \n");
echo("</pre>\n");

