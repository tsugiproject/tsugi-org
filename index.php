<?php
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Output;

// Help the installer through the setup process 
require "check.php" ; 

require_once "top.php";
require_once "nav.php";

// Help the installer through the setup process 
require_once "tsugi/admin/sanity-db.php";
?>
<div id="container">
 <a name="index">
 <div id="page1" class="jumbotron">
    <a name="index"></a>
<!--
<div style="margin-left: 10px; float:right">
<iframe width="400" height="225" src="https://www.youtube.com/embed/tuXySrvw8TE?rel=0" frameborder="0" allowfullscreen></iframe>
</div>
-->
<h1>A Framework for Building Learning Tools</h1>
<p>Welcome to the Tsugi project. 
Our goal is to build a scalable multi-tenant "tool" hosting environment based on the 
emerging IMS standards to help move the industry toward a 
<a href="http://www.ngdle.org" target="_blank">Next Generation Digital Learning Environment</a> (NGDLE).  </p>

<ul>
<li><p>Video Presentation: 
<a href="https://www.youtube.com/watch?v=R-SCdBaF-WY&list=PLysC4Zd9-FmDiBsX9o4shY48iM4PFLfsQ" target="_blank">
IMS Digital Revolution</a>
</p></li>
<li><p>Video Presentation:
<a href="https://www.youtube.com/watch?v=EfSsFs-2lqg" target="_blank">IMS LTI as Research</a></p></li>
<li><p>Video Presentation: 
<a href="https://www.youtube.com/watch?v=iDcoWH9PO6I&amp;index=2&amp;list=PLlRFEj9H3Oj5WZUjVjTJVBN18ozYSWMhw"
target="_blank">Tsugi Overview</a>
</p></li>
</ul><p>If you want to see this code actually working, you can play with the online demo:</p>

<ul>
<li><p><a href="https://lti-tools.dr-chuck.com/tsugi/" target="_blank">https://lti-tools.dr-chuck.com/tsugi/</a></p></li>
</ul><p>Please do not use the testing key/secret for anything that is production.   The data associated with the key "12345" 
is regularly cleared off this system without any notification.   If you want to use this system as a test, 
you can log in to this site and request an account to use with your IMS LTI compatible LMS.  
Here is some LTI 1.0 documentation as to how to use these tools when you have a key / secret:</p>

<ul>
<li><p><a href="md/LAUNCHING.md">Configuring LTI 1.0 Launches</a></p></li>
</ul>
<p>
  You can join the Tsugi developer's list at
  <ul>
    <li><p><a href="https://groups.google.com/a/apereo.org/forum/#!forum/tsugi-dev" target="_blank">Google Group</a> - once you 
    have joined the group you can send email to tsugi-dev@apereo.org.</p></li>
  </ul>
</p>
</div>
 <div id="page2" class="jumbotron">
        <a name="docs"></a>
    <div class="page-padding"></div>
<p>
<ul>
<li><p><a href="tsugi/lessons.php">Developer Tutorials</a></p></li>
<li><p><a href="docs/repos.php">Tsugi Repositories in GitHub</a></p></li>
<li><p><a href="docs/install.php">Installing Tsugi</a></p></li>
<li><p><a href="docs/login.php">Using  Google Login</a></p></li>
<!--
<li><p><a href="../md/CHECKOUT_ALL.md">Checking Code Out</a></p></li>
-->
<li><p><a href="md/CODING.md">Coding Style</a></p></li>
<li><p><a href="md/DEVELOP.md">How to Develop</a></p></li>
<li><p><a href="md/GITHUB.md">Installing Git</a></p></li>
<li><p><a href="md/I18N.md">Internationalizing a Tsugi Tool</a></p></li>
<li><p><a href="md/LAUNCHING.md">Launching a Tsugi Tool from an LMS</a></p></li>
<li><p><a href="md/PHPDOC.md">How to use PHP Doc</a></p></li>
<li><p><a href="md/README_CANVAS_APP_STORE.md">Using Tsugi as an App Store in Canvas</a></p></li>
</ul>
</p>
</div>
 <div id="page3" class="jumbotron">
        <a name="about"></a>
    <div class="page-padding"></div>

<center style="padding-bottom: 20px;">
<a href="http://www.tsugi.org" target="_new">
<img style="width: 80%; max-width:360px;" src="https://www.dr-chuck.net/tsugi-static/img/logos/tsugi-logo-incubating.png">
</a>
</center>
<p>
Tsugi is a framework that handles much of the low-level detail of
building multi-tenant tool that makes use of the 
IMS Learning Tools Interoperability™ (LTI)™ and other learning
tool interoperability standards.
<a href="http://www.tsugi.org" target="_blank">The Tsugi Framework</a> 
provides library and database code to receive and model all
of the incoming LTI data in database tables and sets up a session
with the important information about the LMS, user, and course.
</p>
<p>
If you are interested in 
<a href="support.php">Supporting the Tsugi Effort</a>, please let us know.
<p>
Tsugi is currently an 
<a href="https://www.apereo.org/incubation" target="_blank">incubation project</a> in the 
<a href="http://www.apereo.org/" target="_new">Apereo Foundation</a>.
<p>
Learning Tools Interoperability™ (LTI™) is a
trademark of <a href="http://www.imsglobal.org/" target="_blank">IMS Global Learning Consortium, Inc.</a>
in the United States and/or other countries.
</p>
</div>


</div>
</div>
<?php 
$footerEnd=false;
require_once "foot.php";
?>
<script>
$('body').prepend(' <img src="images/bgbg.jpg" style="z-index: -1000; position: fixed; top:-10%; height: 110%; width: 100%;">');
</script>
<?php
$OUTPUT->footerEnd();
