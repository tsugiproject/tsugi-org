<?php
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Output;
use \Tsugi\Util\U;

require_once "top.php";
//require_once "nav.php";
?>
<style>
.overlay{
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10;
  display: block;
  background-color: rgba(0,0,0,0.5); /*dim the background*/
}

.modal {
       width: 90%;
       height: 100px;
       margin:0 auto;
       display:table;
       position: absolute;
       background-color: white;
       border: 5px black solid;
       padding: 10px;
       left: 0;
       right:0;
       top: 10%;
}

</style>
<script>
function clicked() {
    if (! inIframe() ) {
        alert("This LTI launch has finished with an error.  You can close this window/tab in your browser or perhaps press 'Back' to go back to your learning system");
    }
}
</script>
<?php

$error = U::get($_REQUEST, 'detail');
?>
<body>
<div id="overlay" class="overlay">
<div id="container" class="modal" onclick="clicked();return false;">
<div style="float: right;">
<i id="close_button" class="fas fa-2x fa-window-close" style="display:none;"></i></div>
<script>
if ( ! inIframe() ) {
  document.getElementById("close_button").style.display = "block";
}
</script>

<h1>LTI  Launch Errors</h1>
<p>You get a launch error when there is missing or incorrect data in the launch that
is sent from the Learning Management System / Platform.
</p>
<?php if ( $error ) { ?>
<div class="alert alert-danger">
  <strong>Error:</strong> <?= htmlentities($error) ?>
</div>
<?php } ?>
<?php
if ( strpos($error,'OAuth validation fail key') === 0 && strpos($error, "Expired timestamp") !== false ) {
$out = array();
$delta = preg_match('/.*delta=(\d+).*/', $error, $out) ? $out[1] : false;
?>
<p><b>Detail:</b>
The <a href="https://oauth.net/core/1.0/#nonce" target="_blank">OAuth 1.0</a> protocol used to launch this tool
requires that each launch contain a
timestamp to avoid replay attacks.   Launch data that is prepared by the LMS and sent to the tool generally "expires"
after five minutes (600 seconds) and is rejected by the tool as per the LTI and OAuth protocols.
</p>
<?php if ( $delta ) { ?>
<p>
This launch was received by the tool <b><?= $delta ?></b> seconds after the LMS generated the launch data.
</p>
<?php } ?>
<p>
A common way for this to happen is for the LMS to present you with a screen like "Open External Tool" and 
somehow you take a more than five minutes to press the button.  The simple solution is to go back to the LMS,
refresh the page and launch the tool in less than five minutes.
</p>
<p>
If the delta is quite large and/or you still get this message after refreshing in the LMS and immediately 
launching the tool, it might be the case that either the LMS or tool simply has the incorrect time.   If the clocks
between the LMS and the tool are off by more than five minutes then lauches will almost always fail even if you 
do the "refresh" and press "Open External Tool" immediately.  Again this is a pretty rare occurance since most computers
keep their time in sync using network time sources.
</p>
<?php
} else if ( strpos($error,'OAuth validation fail key') === 0 ) {
?>
<p><b>Detail:</b>
The most typical cause of this error is a mis-match between the OAuth Consumer Key and Secret between
the LMS and tool.   If you are testing a new tool for the first time, there might be a mis-match between 
the URL that the LMS thinking it is launching to and the URL that tool things of itself as serving from.
Sometimes the LMS is launching to an 'https://' url and the tool thinks that it is serving on 'http://'.
The only way to know for sure is to check the base strings on the LMS and tool.  
</p>
<hr/>
<p>
If you are the instructor or administrator of the LMS system, you might want to make a
test launch from your LMS to:
<pre>
URL: https://www.tsugi.org/lti-test/tool.php
Key: 12345
Secret: secret
</pre>
to verify that you know how to properly configure your LMS.  
</p>
<p>
Or you can test to see if you have the correct, URL, key, and secret by launching
the tool from <a href="https://www.tsugi.org/lti-test/lms.php" target="_blank">https://www.tsugi.org/lti-test/lms.php</a>.
</p>
<?php
} else if ( strpos($error,"Session address has expired") === 0 ) {
?>
<p><b>Detail:</b>
Some how in the middle of a login session, there was a disconnect between the session data in your browser
and the session data in the Tsugi server.  A common cause of this is to close your computer and move to
a new location and get a new IP address and refresh the page.   To be safe and secure, when Tsugi notices
that the IP address of your browser changes, it wants you to re-launch the tool.
</p>
<p>
The simple solution is usually to go back into your Learning System and re-launch the tool.  Or you can close
your browser and re-open it and navigate back to the tool.
Tsugi does not use cookies so there is no reason to clear cookies.  Just
close your browser (all the tabs) and reopen it and go back to the tool.
</p>
<?php
} else if ( strpos($error,"could not find issuer") > 0 ) {
?>
<p><b>Detail:</b>
The launch included an <b>issuer</b> value that was not registered with this Tsugi server.
</p>
<?php
} else if ( strpos($error,"Found issuer, but did not find corresponding deployment") === 0 ) {
?>
<p><b>Detail:</b>
The launch included an issuer value that is registered with this server, but the launch
included a <b>deployment_id</b> that is not registered on this server.
</p>
<p>
If you are the Tsugi administrator, to do LTI Advantage launches, it is a two-step
process.  First you must add an Issuer and then add a key that referencees the issuer
and defines the deployment_id.
</p>
<?php
} else if ( strpos($error,"OAuth nonce error") === 0 ) {
?>
<p><b>Detail:</b>
The <a href="https://oauth.net/core/1.0/#nonce" target="_blank">OAuth 1.0</a> protocol used to launch this tool
requires that each launch contain a
timestamp and a single-use token called a 
<a href="https://en.wikipedia.org/wiki/Cryptographic_nonce" target="_blank">Cryptographic nonce</a>.  In order
to avoid replay attacks, LTI and OAuth insist that once a nonce launch has been received, it cannot be reused.
So the second time that Tsugi receives the same <b>oauth_nonce</b> value on the launch, it rejects the launch.
The mistake is in the LMS that allowed you to send the same launch data twice.  Usually you can go back to the LMS,
refresh the launch page and get a new <b>oauth_nonce</b> and do a successful launch.
</p>
<p>
The people who wrote the LMS you are using need to fix their bug.  Nearly all LTI tools <i>ignore</i> the 
<b>oauth_nonce</b> and so LMS vendors never realize their bug in normal testing.  Those LTI tools
are vulnerable to a reply attack.  But Tsugi <i>does</i> process the <b>oauth_nonce</b> value exactly per the
OAuth spec - so (a) Tsugi tools are not vulnerable to a simple replay attack and (b) you see this message.
</p>
<p>
If you go to the LMS page, and launch twice and get this error and then do a third launch after a refresh
and a launch and it works - please file a bug report with your LMS vendor :).
</p>
<?php
} else if ( strpos($error,"Missing required user_id") === 0 ) {
?>
<p><b>Detail:</b>
This is often caused by one of two reasons. Sometimes the LMS is doing an "anonymous launch"
and is simply not including the "user_id" which is required by the tool.  The solution in
this case is to reconfigure the LMS to include the required information on the launch.
</p>
<p>
Another less common situation is when the LMS is sending a "Content Item" / "App Store" / 
"Deep Link" request (which is not supposed to include a "user_id" value but sending the request
to a tool end point and not an app store endpoint.  Usually in Tsugi, these kinds of
provisioning requests need to be sent to a URL of the form "/tsugi/lti/store/".
</p>
<?php
} else if ( strpos($error,"Could not find tenant/key ") === 0 ) {
?>
<p><b>Detail:</b>
The tool has received a broken LTI 1.3 launch.  This typically happens when you have defined an Issuer in
Tsugi but have not created and/or updated a Key/Tenant and associated it with the issuer/clientid/deployment_id combination.
(<a href="md/ADVANTAGE.md" target="_blank">LTI Advantage Documentation</a>)
</p>
<?php
} else if ( strpos($error,"Session expired - please re-launch") === 0 || 
            strpos($error,"Session has expired") === 0 ) 
{
?>
<p><b>Detail:</b>
This error can mean one of several things happenned.  You might have simply stopped using this page for a long while
and your session expired.  Alternatively, something about your Internet connection changed or you switched to a different
browser.  Perhaps your computer went to sleep and woke back up with a different network address.  Or perhaps you tried
to bookmark a direct link into this tool - which does not work.
</p>
<p>
To solve this go back to the learning system where you originally launched the tool and relaunch the tool.
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
<p><b>Detail:</b> This tool did not receive a proper launch and there either was no tool session
from a prior launch.   If this is the initial lauch, the LMS probably sent broken data.  If you have been 
using the tool for a while, it may have lost its session due to a bug in the tool.  If is the first time
your are testing the tool you may have omitted the trailing slash on the launch URL.
</p>
<?php
} else if ( stripos($error,"Client not authorized in requested context") === 0 ) {
?>
<p><b>Detail:</b> This is a Canvas-specific error message - there is something mis-configured between
yout <b>Client ID</b> and <b>Deployment ID</b>.
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
</div>
</body>
<?php 
// require_once "foot.php";

