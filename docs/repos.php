<?php
require_once "../top.php";
require_once "../nav.php";
?>
<div id="container">
<h1>Tsugi in Github</h2>
<p>Tsugi has two organizations and a number of repositories.  This page serves as 
an index into those repositories.
The <a href="https://github.com/tsugiproject/" target="_blank">tsugiproject</a>
organization hosts the varius implementations of Tsugi and the 
The <a href="https://github.com/tsugitools/" target="_blank">tsugitools</a>
organization hosts ready-to-install Tsugi tools that can be installed using the
<a href="https://github.com/tsugiproject/tsugi" 
target="_blank">Tsugi Administration, Developer, and Management Console</a>.
</p>
<h2>General Tsugi Repositories</h2>
<ul>
<li><p><a href="https://github.com/tsugitools/" 
target="_blank">Tsugi Tools</a><br>
When you install Tsugi, it can auto-install tools from this repository
into your instance of Tsugi using <b>github</b> from the Administrator interface.
</p>
</li>
<li><p><a href="https://github.com/tsugiproject/tsugi-static" 
target="_blank">Tsugi Static Content</a><br>
The static content (JavaScript, Images, CSS, etc.) for Tsugi is shared across the 
various versions so it is kept in a separate repository.  It is also hosted using
<a href="http://www.cloudflare.com" target="_blank">CloudFlare</a> for high performane
cached access worldwide to reduce load on Tsugi application servers.  The URL for the
static content is:
<p>
<a href="http://www.dr-chuck.net/tsugi-static/" target="_blank"
>http://www.dr-chuck.net/tsugi-static/</a>
</p>
<p>
As part of configuring a Tsugi instance, you can control what URL is used to 
load static content.   The default is to pull this content from Cloudflare.
If you want ot operate truly offline with no developer connection, you can 
checkout and change <b>config.php</b> point to a local copy of the static content.
</p>
<li><p><a href="https://github.com/tsugiproject/tsugi-org" 
target="_blank">The source for the tsugi.org web site</a><br>
This is the course code for <a href="http://www.tsugi.org">www.tsugi.org</a>.
This web site is also a Tsugi application using Tsugi as an embedded LMS
to host and track Tsugi tutorials and badges.
</p>
</li>
</ul>
<h2>Tsugi in PHP</h2>
<ul>
<li><a href="https://github.com/tsugiproject/tsugi" 
target="_blank">Tsugi Administration, Developer, and Management Console</a><br>
This is the starting point for any Tsugi effort.  It sets up the databases, 
manages keys, installs and hosts tools, functions as a test platform, 
developer test harness
and lets you manage your
Tsugi installation.  Even if you are using 
<a href="https://github.com/tsugiproject/tsugi-java" target="_blank"
>Tsugi Java</a>, 
<a href="https://github.com/tsugiproject/tsugi-node" target="_blank"
>Tsugi Node</a>, 
or
<a href="https://github.com/tsugiproject/tsugi-laravel-sample" target="_blank"
>Tsugi Laravel</a>, you need to install the administration console to set things
up for the Tsugi Run time.
</p>
<p>
The Administration console also contains support for an LTI 2.0 provider,
a Common Cartridge Export and IMS ContentItem App Store for all tools.
</p>
</li>
<li><a href="https://github.com/tsugiproject/tsugi-php" 
target="_blank">Tsugi PHP Library</a><br>
This contains the library code for the 
<a href="http://do1.dr-chuck.com/tsugi/phpdoc/namespaces/Tsugi.html" target="_blank">
Tsugi PHP library</a>.  This is used in all of the PHP applications.  
This library is released on 
<a href="https://packagist.org/packages/tsugi/lib" target="_blank">Packagist</a>
and can be added to your <b>composer.json</b> as follows:
<pre>
"require": {
    "tsugi/lib": ">=0.3.1"
}
</pre>
</p>
</li>
<li><p><a href="https://github.com/tsugiproject/tsugi-php-module" target="_blank">Tsugi Module (Sample)</a><br/>
Copy this if you want to start a fresh Tsugi Module from scratch.  If you are building
a new tool from scratch, you should build it as a "Tsugi Module" following all 
of the Tsugi style guidance, using the Tsugi browser environment, and making 
full use of the Tsugi framework. This repository contains a basic 
"Tsugi Module" you can use as a starting point.</p></li>
<li><p><a href="https://github.com/tsugiproject/tsugi-php-standalone" target="_blank">Tsugi-Enabled Application (sample)</a><br/>
You can also use Tsugi as a library and  add it to a few places in an existing application. 
This repository contains sample code showing how to use Tsugi as a library in an existing 
application.</p></li>
<li><p><a href="https://github.com/tsugiproject/tsugi-php-samples" target="_blank">Tsugi Sample Code Snippets</a><br/>
These are relatively short bits of code that you can look at for example code as you write your
own Tsugi Module.</p></li>
<li><p><a href="https://github.com/tsugiproject/tsugi-php-exercises" target="_blank">Tsugi Developer Exercises</a><br/>
This is a set of exercises of increasing difficulty suitable for a class or 
workshop.  Working solutions are provided online.  Source code for working solutions
is only available to inctructors that contact Dr. Chuck.</p></li>

<li><p>
<a href="https://github.com/tsugiproject/tsugi-laravel-sample" target="_blank">Tsugi Laravel</a><br/>
This is the beginnings of a library to make the
<a href="https://github.com/csev/tsugi-php" target="_blank">Tsugi PHP</a>
library usable in Laravel applications.
</p>
</ul>
<h1>Tsugi In Other Languages</h2>
<p>
Tsugi is best supported in the PHP languages but these languages are emerging and will evolve as there
is interest.
<ul>
<li><p>
<a href="https://github.com/tsugiproject/tsugi-java-servlet">Tsugi Java</a><br/>
This is a reasonably complete implementation of the Tsugi run-time in Java.  
It shares low level IMS libraries with Sakai and is ready for production use.</p></li>
<li><p>
<a href="https://github.com/tsugiproject/tsugi-node-sample">Tsugi NodeJS</a><br/>
- This is early pre-emergent code.</p></li>
<li><p>
<a href="https://github.com/tsugiproject/tsugi-python-test">Tsugi Functionality Test</a><br/>
While there is not yet a Python version of Tsugi, there is a functionality test that 
exercises the various Tsugi implementations with LTI launches and does validation that they
each work with the Tsugi data model properly.
</p>
</li>
</ul>

</div>
<?php
$OUTPUT->footer();
