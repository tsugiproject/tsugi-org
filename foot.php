<?php 

$foot = '
<p style="font-size: 75%; margin-top: 5em;">
Copyright Creative Commons Attribution 3.0 - Charles R. Severance
</p>';

$OUTPUT->setAppFooter($foot);

$OUTPUT->footerStart();
?>
<script>$(document).ready(function() {
  function filterPath(string) {
    return string
      .replace(/^\//,'')  
      .replace(/(index|default).[a-zA-Z]{3,4}$/,'')  
      .replace(/\/$/,'');
  }
  $('a[href*=#]').each(function() {
    if ( filterPath(location.pathname) == filterPath(this.pathname)
    && location.hostname == this.hostname
    && this.hash.replace(/#/,'') ) {
      var $targetId = $(this.hash), $targetAnchor = $('[name=' + this.hash.slice(1) +']');
      var $target = $targetId.length ? $targetId : $targetAnchor.length ? $targetAnchor : false;
       if ($target) {
         var targetOffset = $target.offset().top;
         $(this).click(function() {
           $('html, body').animate({scrollTop: targetOffset}, 2000);
           return false;
         });
      }
    }
  });
});</script>
<?php
if ( (! isset($footerEnd)) || $footerEnd ) $OUTPUT->footerEnd();
