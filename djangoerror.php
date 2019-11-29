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
<h1>Django Tsugi Launch Errors</h1>
<p>This page provides detail when there is an error with Tsugi launching a Django application.
</p>
<?php if ( $error ) { ?>
<div class="alert alert-danger">
  <strong>Error:</strong> <?= htmlentities($error) ?>
</div>
<?php } ?>
<?php
$error_detail = array(
"This is a Launch URL, expecting a POST with a JWT to initiate a launch" =>
"You have used a GET request to a URL that is internal and used to receive a launch
with a signed JSON Web Token (JWT) from Tsugi to initiate a session in the Django application. ",

"This URL is expecting a POST with a JWT to initiate a launch" =>
"The proper way to communicate a JWT to this tool is with a form parameter of JWT.",

"Could not load header from JWT" =>
"This is an improperly formed JSON Web Token as it does not have a header area.",
"The JWT is missing a key ID (kid)" =>
"This launch is missing a 'kid' value in its header to look up a public key in a JWK Keyset.",

"Could not load keyset from" =>
"This launch retrieves a JWK key set from this URL and expects to find JSON data
so it can retrieve the appropriate public key to validate the launch.  You can try to
retrieve the keyset URL by hand to see if it contains the properly formatted JSON.
The Django Tsugi may be improperly configured and looking for its keyset at the wrong URL.",

"Could not parse keyset from" =>
"Django Tsugi was able to retrieve its keyset URL but the data was in the wrong format.
You can try to
retrieve the keyset URL by hand to see if it contains the properly formatted JSON.
The Django Tsugi may be improperly configured and looking for its keyset at the wrong URL.",

"Could not extract kid" =>
"Tsugi Django retrieved and parsed the keyset but could not find an entry that matched
the incoming kid from the JSON Web Token. You can try to
retrieve the keyset URL by hand to see if it contains the properly formatted JSON.
The Django Tsugi may be improperly configured and looking for its keyset at the wrong URL.",

"Could not validate JSON Web Token signature" =>
"This indicates that the public key that was provided could not validate the incoming
JSON Web Token.",
);

foreach($error_detail as $message => $text ) {
    if ( strpos($error,$message) === false ) continue;
    echo("<p><b>Detail:</b>\n");
    echo($text);
    echo("\n</p>\n");
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

