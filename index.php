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
<li>Video Presentation: 
<a href="https://www.youtube.com/watch?v=R-SCdBaF-WY&list=PLysC4Zd9-FmDiBsX9o4shY48iM4PFLfsQ" target="_blank">
IMS Digital Revolution</a>
</li>
<li>Video Presentation:
<a href="https://www.youtube.com/watch?v=EfSsFs-2lqg" target="_blank">IMS LTI as Research</a></li>
<li>Video Presentation: 
<a href="https://www.youtube.com/watch?v=iDcoWH9PO6I&amp;index=2&amp;list=PLlRFEj9H3Oj5WZUjVjTJVBN18ozYSWMhw"
target="_blank">Tsugi Overview</a>
</li>
</ul><p>If you want to see this code actually working, you can play with the online demo:</p>

<ul>
<li><a href="https://lti-tools.dr-chuck.com/tsugi/" target="_blank">https://lti-tools.dr-chuck.com/tsugi/</a></li>
</ul><p>Please do not use the testing key/secret for anything that is production.   The data associated with the key "12345" 
is regularly cleared off this system without any notification.   If you want to use this system as a test, 
you can log in to this site and request an account to use with your IMS LTI compatible LMS.  
Here is some LTI 1.0 documentation as to how to use these tools when you have a key / secret:</p>

<ul>
<li><a href="md/LAUNCHING.md">Configuring LTI 1.0 Launches</a></li>
</ul>
<p>
  You can join the Tsugi developer's list at
  <ul>
    <li><a href="https://groups.google.com/a/apereo.org/forum/#!forum/tsugi-dev" target="_new">Google Group</a> - once you 
    have joined the group you can send email to tsugi-dev@apereo.org.</li>
  </ul>
</p>
<?php 
require_once "foot.php";
