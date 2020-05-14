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
<pre><code>http://localhost
https://www.py4e.com
https://www.wa4e.com</code></pre>
<p>You don't need a port number for the JavaScript origin.</p>
<p>Here are my sample redirect URIs:</p>
<pre><code>
http://localhost/tsugi/login
https://www.py4e.com/tsugi/login
http://localhost/py4e/tsugi/login
https://www.wa4e.com/tsugi/login
http://localhost/wa4e/tsugi/login</code></pre>
<p>Again, the port seems not to matter.
This works both for localhost instances as well as instances on the
web which is nice for testing.
</p>
<p>
Also, in the beginning, the redirect URI for Tsugi ended in <b>/tsugi/login.php</b> - 
for more recent Tsugi installs, you can drop the php at the end
unless you set:
<pre><code>$CFG-&gt;google_login_new = false;</code></pre>
in your <b>config.php</b>.
</p>

</div>
<?php
$OUTPUT->footer();
