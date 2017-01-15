<?php
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Output;

// Help the installer through the setup process 
require "check.php" ; 

require_once "top.php";
?>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<?php
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
<li><p><a href="#about">About Tsugi</a></p></li>
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
<a href="#support">Supporting the Tsugi Effort</a>, please let us know.
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

<div id="page4" class="jumbotron">
    <a name="support"></a>
    <div class="page-padding"></div>
<h1>Supporting Tsugi</h1>
<p>
This is an open source effort that is still in active development and evolution.
We can use lots of help and financial support.  Here are some ways to support
Tsugi.
<ul>
<li><p>Join the 
<a href="https://www.apereo.org/" target="_blank">Apereo Foundation</a>.
Tsugi is an incubating project within Apereo and any support you give
to Apereo by joining, participating, and/or coming to Apereo meetings
helps the cause of all Apereo projects.
</p></li>
<li><p>
Contribute funds to the 
<a href="https://www.apereo.org/" target="_blank">Apereo Foundation</a>
tagged for the Tsugi project.  This give us funds to spend on
things like students, support travel to meetings, graphic design, etc.
</p></li>
<li><p>
If you want to fund Dr. Chuck's research efforts (like a grant) on Tsugi at the
<a href="http://www.si.umich.edu" taget="_blank">University of Michigan
School of Information</a> please contact him.
</p></li>
<li><p>
If you are a granting or philanthropic organization interested in improving 
educational technology and think that Tsugi might be a good addition to 
your project portfolio, please contact Chuck to get a proposal developed.
</p></li>
<li><p>
If you have an educational technology grant and would like to use Tsugi 
and provide some funds to our project to help make your project a success,
it would be much appreciated.  Again, contact Chuck.
</p></li>
<li><p>
If none of the above make sense to you, just hit the "donate" button below
and I will make sure the funds are applied as best appropriate.
</p></li>
</ul>
</p>
<center>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</center>


</div>

</div>
</div>
<?php 
$footerEnd=false;
require_once "foot.php";
?>
<script>
$('body').prepend('<img src="images/bgbg.jpg" onload="this.style.opacity=\'1\';" \
  style=" z-index: -1000; position: fixed; top:-10%; height: 110%; width: 100%; \
   opacity:0; -moz-transition: opacity 2s; -webkit-transition: opacity 2s; \
  -o-transition: opacity 2s; transition: opacity 2s; \
">');
</script>
<?php
$OUTPUT->footerEnd();
