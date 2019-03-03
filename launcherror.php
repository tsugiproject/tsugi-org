<?php
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Output;
use \Tsugi\Util\U;

require_once "top.php";
require_once "nav.php";

$error = U::get($_REQUEST, 'detail');
?>
<div id="container">
<h1>Tsugi Launch Errors</h1>
<p>You get a launch error when there is missing or incorrect data in the launch that
is sent from the Learning Management System / Platform.
</p>
<?php if ( $error ) { ?>
<div class="alert alert-danger">
  <strong>Error:</strong> <?= htmlentities($error) ?>
</div>
<?php } ?>
<p>
One of the first things to check is to make sure that both the tool and LMS / platform
are officially certifified at 
<a href="https://www.imsglobal.org/cc/statuschart.cfm">https://www.imsglobal.org/cc/statuschart.cfm</a>
</p>
<?php
if ( strpos($error,'OAuth validation fail key') === 0 ) {
?>
<p>The most typical cause of this error is a mis-match between the OAuth Consumer Key and Secret between
the LMS and tool.   If you are testing a new tool for the first time, there might be a mis-match between 
the URL that the LMS thinking it is launching to and the URL that tool things of itself as serving from.
Sometimes the LMS is launching to an 'https://' url and the tool thinks that it is serving on 'http://'.
The only way to know for sure is to check the base strings on the LMS and tool.  You might want to make a
test launch from your LMS to:
<pre>
URL: https://www.tsugi.org/lti-test/tool.php
Key: 12345
Secret: secret
</pre>
or launch your tool from <a href="https://www.tsugi.org/lti-test/lms.php" target="_blank">https://www.tsugi.org/lti-test/lms.php</a>.
</p>
<?php
}
?>
</div>
<?php 
require_once "foot.php";

