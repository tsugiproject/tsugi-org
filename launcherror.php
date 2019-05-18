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
<?php
if ( strpos($error,'OAuth validation fail key') === 0 ) {
?>
<p><b>Detail:</b>
The most typical cause of this error is a mis-match between the OAuth Consumer Key and Secret between
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
} else if ( strpos($error,"Could not find tenant/key ") === 0 ) {
?>
<p><b>Detail:</b>
The tool has received a broken LTI 1.3 launch.  This typically happens when you have defined an Issuer in
Tsugi but not create a Key/Tenant for the issuer/clientid/deployment_id combination.
(<a href="md/ADVANTAGE.md" target="_blank">LTI Advantage Documentation</a>)
</p>
<?php
} else if ( strpos($error,"Invalid Key Id (header.kid), could not find public key") === 0 ) {
?>
<p><b>Detail:</b>
The tool has received a broken LTI 1.3 launch.  A common cause of this is when you are using the
IMS certification suite where they are sending broken launches on purpose to insure the tool has
a suitable error message (like this).
</p>
<?php
} else if ( strpos($error,"Bad LTI version") === 0 ) {
?>
<p><b>Detail:</b> The tool has received a launch that looks like LTI 1.3 but is missing or has the incorrect version.
A common cause of this is when you are using the
IMS certification suite where they are sending broken launches on purpose to insure the tool has
a suitable error message (like this).
<p>
If you are receiving this message from a launch from an acutal LMS it is lilely mis-configured or generating
bad launches.
</p>
<?php
} else if ( strpos($error,"This tool should be launched from a learning system using LTI") === 0 ) {
?>
<p><b>Detail:</b> This tool did not receive a proper launch and there wither was no tool session
from a prior lauch.   If this is the initial lauch, the LMS probably sent broken data.  If you have been 
using the tool for a while, it may have lost its session due to a bug in the tool.
</p>
<?php
} else if ( strpos($error,"Missing") === 0 ) {
?>
<p><b>Detail:</b> This tool did not receive a required item for this launch.   Your LMS or testing system
is probably sending a launch improperly.
</p>
<?php
}
?>
<p>
One of the first things to check is to make sure that both the tool and LMS / platform
are officially certified at 
<a href="https://www.imsglobal.org/cc/statuschart.cfm" target="_blank">https://www.imsglobal.org/cc/statuschart.cfm</a>
</p>
</div>
<?php 
require_once "foot.php";

