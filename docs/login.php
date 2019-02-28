<?php
require_once "../top.php";
require_once "../nav.php";
?>
<div id="container">

<h2>Using Google's Login</h2>
<p>
<a href="https://console.developers.google.com/apis/credentials" 
target="_blank">https://console.developers.google.com/apis/credentials</a>,
create a new OAuth 2.0 credential for a web application, get the key 
and secret, and put them into the Tsugi configuration file:</p>
<pre><code>$CFG-&gt;google_client_id = '96041-nl...jpjj8jlv4.apps.googleusercontent.com';
$CFG-&gt;google_client_secret = '6Q7w...ESrl29a';
$CFG-&gt;google_map_api_key = 'Ve8eH49498....43cIA9IGl8';</code></pre>
<p>Make sure to add the proper JavaScript origins and authorized redirect urls
in your Google credentials configuration.</p>
<p>As a sample, 
here are some of my JavaScript origins:</p>
<pre><code>https://online.dr-chuck.com
http://localhost
https://online.dr-chuck.com
https://pr4e.dr-chuck.com
https://lti-tools.dr-chuck.com
https://www.php-intro.com
https://pylearn.sites.uofmhosting.net
https://www.py4e.com
https://www.wa4e.com</code></pre>
<p>You don't need a port number for the JavaScript origin.</p>
<p>Here are my sample redirect URIs:</p>
<pre><code>http://localhost/GoogleLogin/index.php
http://localhost/tsugi/login.php
https://lti-tools.dr-chuck.com/tsugi/login.php
https://online.dr-chuck.com/login.php
https://pr4e.dr-chuck.com/tsugi/login.php
https://pr4e.dr-chuck.com/GoogleLogin/index.php
https://www.php-intro.com/tsugi/login.php
http://localhost/pythonlearn/tsugi/login.php
https://www.py4e.com/tsugi/login.php
https://www.wa4e.com/tsugi/login.php
http://localhost/wa4e/tsugi/login.php</code></pre>
<p>Again, the port seems not to matter.</p>
<p>
This works both for localhost instances as well as instances on the
web which is nice for testing.
</p>

</div>
<?php
$OUTPUT->footer();
