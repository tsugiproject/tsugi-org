<?php
require_once "../top.php";
require_once "../nav.php";
?>
<div id="container">
<h1>Installing TSUGI</h1>
<p>I have recorded an
<a href="https://www.youtube.com/watch?v=Na_QDXp-Y7o&index=1&list=PLlRFEj9H3Oj5WZUjVjTJVBN18ozYSWMhw" target="_blank">Installation video</a>
describing the install/config steps
for this software on YouTube:
<h2>Pre-Requisites</h2>
<ul>
<li>
<p><a href="../md/GITHUB.md">Install GIT</a> so that it works at the command prompt.</p>
</li>
<li>
<p>Install a PHP/MySQL Environment like XAMPP / MAMP following the
instructions at:</p>
<p><a href="http://www.wa4e.com/install.php" target="_blank">http://www.wa4e.com/install.php</a></p>
</li>
</ul>
<p>To install this software follow these steps:</p>
<h2>Installation</h2>
<ul>
<li>
<p>Check the code out from GitHub and put it in a directory where
your web server can read it</p>
<pre><code>git clone https://github.com/tsugiproject/tsugi.git</code></pre>
</li>
<li>
<p>Create a database and get authentication info for the database</p>
<pre><code>CREATE DATABASE tsugi DEFAULT CHARACTER SET utf8;
GRANT ALL ON tsugi.* TO 'ltiuser'@'localhost' IDENTIFIED BY 'ltipassword';
GRANT ALL ON tsugi.* TO 'ltiuser'@'127.0.0.1' IDENTIFIED BY 'ltipassword';</code></pre>
</li>
<li>
<p>Copy the file config-dist.php to config.php and edit the file
to put in the appropriate values.  Make sure to change all the secrets.
If you are just getting started turn on DEVELOPER mode so you can launch
the tools easily.  Each of the fields is documented in the config-dist.php
file - here is some additional documentation on the configuration values:</p>
<p><a href="http://do1.dr-chuck.com/tsugi/phpdoc/classes/Tsugi.Config.ConfigInfo.html"
target="_blank">http://do1.dr-chuck.com/tsugi/phpdoc/classes/Tsugi.Config.ConfigInfo.html</a></p>
</li>
<li>
<p>Go to the main page, and click on &quot;Admin&quot; to make all the database
tables - you will need the Admin password you just put into config.php
If all goes well, lots of tables should be created.  You can run upgrade.php
more than once - it will automatically detect that it has been run.</p>
</li>
<li>At that point you can play with and/or develop new tools</li>
</ul>
<p>Note: Make sure that none of the folders in the path to the tsugi
folder have any spaces in them.  You may get signature errors
if you use folders with blanks in them.</p>
</div>
<?php
$OUTPUT->footer();
