<?php
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Output;

require_once "top.php";
require_once "nav.php";

?>
<div id="container">
<h1>Tsugi Launch Errors</h1>
<p>You get a launch error when there is missing or incorrect data in the launch that
is sent from the Learning Management System / Platform.
</p>
<p>
One of the first things to check is to make sure that both the tool and platform
are officially certifified at 
<a href="https://www.imsglobal.org/cc/statuschart.cfm">https://www.imsglobal.org/cc/statuschart.cfm</a>
</p>
</div>
<?php 
require_once "foot.php";

