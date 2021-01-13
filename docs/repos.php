<?php
require_once "../top.php";
require_once "../nav.php";
?>
<div id="container">
<h1>Tsugi in Github</h1>
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
<a href="http://www.cloudflare.com" target="_blank">CloudFlare</a> for high performance
cached access worldwide to reduce load on Tsugi application servers.  The URL for the
static content is:
<p>
<a href="https://static.tsugi.org/" target="_blank"
>https://static.tsugi.org/</a>
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
developer test harness and lets you manage your Tsugi installation.  
</p>
<p>
The Administration console also contains support for an IMS LTI versions
a Common Cartridge Export and IMS ContentItem / Deep Linking App Store for all tools.
</p>
</li>
<li><a href="https://github.com/tsugiproject/tsugi-php" 
target="_blank">Tsugi PHP Library</a><br>
This contains the library code for the 
<a href="http://do1.dr-chuck.com/tsugi/phpdoc/" target="_blank">
Tsugi PHP library</a>.  This is used in all of the PHP applications.  
This library is released on 
<a href="https://packagist.org/packages/tsugi/lib" target="_blank">Packagist</a>
and can be added to your <b>composer.json</b> as follows:
<pre>
"require": {
    "tsugi/lib": ">=0.5.1"
}
</pre>
</p>
</li>
<li><a href="https://github.com/tsugiproject/koseu-php" 
target="_blank">Koseu LMS/MOOC Platform</a><br>
The code to support adding a Tsugi-powered LMS to a web site will be re-factored into 
into own project named "Koseu (코스)".  This will include the lessons, badges, discussion,
topics, and gradebook functionality.
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
<a href="https://github.com/tsugiproject/tsugi-python-test">Tsugi Functionality Test</a><br/>
While there is not yet a Python version of Tsugi, there is a functionality test that 
exercises the various Tsugi implementations with LTI launches and does validation that they
each work with the Tsugi data model properly.
</p>
</li>

</ul>
<h2>Tsugi In The Cloud</h2>
<p>
We have a number of repositories that include varius resources for Tsugi into the cloud on
Amazon Web services.  These scripts are used to construct
the various production services at 
<a href="https://www.tsugicloud.org" target="_blank">www.tsugicloud.org</a> or
<a href="https://www.learnxp.com" target="_blank">www.learnxp.com</a>
(a Tsugi 
<a href="https://www.apereo.org/affiliates/learning-experiences" target="_blank">commercial partner</a>).
<ul>
<li><p>
<a href="https://github.com/tsugicloud/" target="_blank">
The overall TsugiCloud Repository</a>
</p>
</li>
<li><p>
<a href="https://github.com/tsugicloud/ami-sql" target="_blank">
Building a Tsugi AMI on Amazon Web Services</a> - 
This repo is used to produce the Amazon-hosted scalable instances of Tsugi
for 
<a href="https://www.tsugicloud.org" target="_blank">TsugiCloud</a> and
<a href="https://www.learnxp.com" target="_blank">Learning Experiences</a>.
These instances make use of AWS
<a href="https://aws.amazon.com/rds/aurora" target="_blank">Aurora</a>,
<a href="https://aws.amazon.com/memcached/" target="_blank">Memcached</a>,
<a href="https://aws.amazon.com/efs" target="_blank">Elastic File System</a>,
<a href="https://aws.amazon.com/elasticloadbalancing" target="_blank">Application Load Balancing</a>
to deploy a highly available, scalable and resiliant Tsugi deployment.
For your own Tsugi deployments you should consider this as examples and
starting points.
</p>
</li>
<li><p>
<a href="https://github.com/tsugiproject/docker-php" target="_blank">
Docker recipes</a>
This repo describes how to produce Docker containers for Tsugi PHP.
These can be used locally to create versions ranging from
<a href="https://dev.tsugicloud.org/">developer instances of Tsugi</a>
to versions that accept user configuration data for a scalable production
deployment. 
</p>
</li>
</ul>
<p>
At this point scalable instances are best run on AWS since there are so many supporting services
that keep socts low while allowing high scalability.  Docker/Kubernetes on Azure  or
Google Cloud is just so much less mature so production deployments simply take more resources
and DevOps talent.   
</p>

<h1>Tsugi In Other Languages</h1>
<p>
Tsugi is best supported in the PHP languages but these languages are emerging and will evolve as there
is interest.
<ul>
<li><p>
<a href="https://github.com/tsugiproject/tsugi-java-servlet">Tsugi Java</a><br/>
This is a reasonably complete implementation of the Tsugi run-time in Java.  
It shares low level IMS libraries with Sakai and is ready for production use.</p></li>
</ul>
<p>
Former language efforts in Tsugi beyond PHP and Java are now deprecated.

<h1>Deprecated Work</h1>
<p>
This is initial work that was produced in hopes that a community would emerge around it
but when there was insufficient interest, the honest thing to do is deprecate it.
<ul>
<li><p>
<a href="https://github.com/tsugicloud/kube" target="_blank">
Kuberbetes/Docker recipes</a>
A great deal of effort was made to host Tsugi at scale using Kubernetes
on Google Cloud. The recipes are solid as far as they go but Google
Cloud is just too immature to be the suggested technique for hosting Tsugi at
scale.  Amazon has serverless Aurora, Elastic File System, and Elasticache -
they are both very cost effective and simple to deploy and use.  At some point
if the Google Cloud (or Azure) ends up with a better suite of support
services, this effort can be restarted.  Alternatvely if there is a region where
Kubernetes is the *only* solution available - this can be evolved into production
ready capabilities with some additional effort.
</p>
</li>
<li><p>
<a href="https://aws.amazon.com/dynamodb" target="_blank">DynamoDB</a> was initially used
for sessions storage / failover but ultimately it was slow and expensive compared to 
<a href="https://aws.amazon.com/memcached/" target="_blank">Memcached</a>.
</p>
</li>
<li><p>
<a href="https://github.com/tsugiproject/tsugi-node-sample">Tsugi NodeJS</a><br/>
- This was pre-emergent code but is deprecated until there is more interest.</p></li>
<li><p>We developed an early Python 3 
<a href="https://github.com/tsugiproject/pytsugi" target="_blank">Python 3</a>
Tsugi implementation and an example of using this library in 
<a href="https://github.com/tsugiproject/pytsugi-web2py" target="_blank">Web2Py</a>
but this is now deprecated.
</p></li>
<li><p>
<a href="https://github.com/tsugiproject/tsugi-laravel-sample" target="_blank">Tsugi Laravel</a><br/>
This is the beginnings of a library to make the
<a href="https://github.com/tsugiproject/tsugi-php" target="_blank">Tsugi PHP</a>
library usable in Laravel applications.
</p>
</li>
</ul>
<p>
As of late 2018, we are designing and will build a next-generation tool hosting framework
based on Tsugi exporting its functionality via web services.  This will allow
a far greater diversity in tool impementation environment choices and better meet
the privacy requirements of the GDPR.
</p>

</div>
<?php
$OUTPUT->footer();
