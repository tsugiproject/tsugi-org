<?php
use \Tsugi\Util\Net;
use \Tsugi\Core\LTIX;
use \Tsugi\UI\Output;

require_once "top.php";
require_once "nav.php";

?>
<div id="container">
<!--
<div style="margin-left: 10px; float:right">
<iframe width="400" height="225" src="https://www.youtube.com/embed/tuXySrvw8TE?rel=0" frameborder="0" allowfullscreen></iframe>
</div>
-->
<h1>Supporting Tsugi</h1>
<p>
This is an open source effort that is still in the early development phases.
We can use lots of help and financial support.  Here are some ways to support
Tsugi.
<ul>
<li>Join the 
<a href="https://www.apereo.org/" target="_blank">Apereo Foundation</a>.
Tsugi is an incubating project within Apereo and any support you give
to Apereo by joining, participating, and/or coming to Apereo meetings
helps the cause of all Apereo projects.
</li>
<li>
Contribute funds to the 
<a href="https://www.apereo.org/" target="_blank">Apereo Foundation</a>
tagged for the Tsugi project.  This give us funds to spend on
things like students, support travel to meetings, graphic design, etc.
</li>
<li>
If you want to fund Dr. Chuck's research efforts (like a grant) on Tsugi at the
<a href="http://www.si.umich.edu" taget="_blank">University of Michigan
School of Information</a> please contact him.
</li>
<li>
If you are a granting or philanthropic organization interested in improving 
educational technology and thing that Tsugi might be a good addition to 
your project portfolio, please contact Chuck to get a proposal developed.
</li>
<li>
If you have an educational technology grant and would like to use Tsugi 
and provide some funds to our project to help make your project a success,
it would be much appreciated.  Again, contact Chuck.
</li>
<li>
If none of the above make sense to you, just hit the "donate" button below
and I will make sure the funds are applied as best appropriate.
</li>
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
<?php 
require_once "foot.php";

