<?php
require_once "../top.php";
require_once "../nav.php";
?>
<div id="container">
<h1>Tsugi Theme</h1>
<p>
There is an interactive web site and YouTube demonstration that can be used to explore the approach being taken:
</p><p>
<ul>
<li><a href="https://www.tsugi.org/theme/" target="_blank">
https://www.tsugi.org/theme/</a></li>
<li><a href="https://youtu.be/NbomiotjbDo" target="_blank">
https://youtu.be/NbomiotjbDo</a></li>
</ul>
</p><p>
The approach is to think of the theme as an abstraction and to dynamically map the theme to a set of global CSS variables and those variables are used throughout the Tsugi markup.  Switching to dark mode is a different mapping between the theme variables and markup CSS variables.
</p><p>
Here are the theme variables - they use “tsugi” as their namespace to allow for an “ims” namespace if and when we build a standard.  These values are ordered from darkest to lightest.
</p><p>
<ul>
<li><b>
tsugi-theme-dark-background</b> - This is the overall page background for dark mode. It is the darkest theme color and it is OK for this to be #000000 or a very dark gray
</li><li><b>
tsugi-theme-dark-text</b> - this color needs to be between dark and dark background - usually it has lower saturation (i.e. closer to gray) than other dark colors
</li><li><b>
tsugi-theme-dark-darker</b> - this color needs to be between dark and dark-background - It could be used for text headers or perhaps used with “dark” to produce a gradient
</li><li><b>
tsugi-theme-dark</b> - this is the primary dark color - if the LMS has a navigation bar with light text on a dark background, this should match that background color
</li><li><b>
tsugi-theme-dark-accent</b> - The accent color can be any color - it cannot be used for anything that matters for accessibility - it could be used to separate sections of a markup or serve as a border
</li><li><b>
tsugi-theme-light-accent</b> - The accent color can be any color - it cannot be used for anything that matters for accessibility -it could be used to separate sections of a markup or serve as a border
</li><li><b>
tsugi-theme-light</b> - this is the primary light color - if the LMS has a navigation bar with light text on a dark background, this should match the text color
</li><li><b>
tsugi-theme-light-lighter</b> - this color needs to be between light and light-background - It could be used for text headers or perhaps used with “light” to produce a gradient
</li><li><b>
tsugi-theme-light-text</b> - this color needs to be between light and light background - usually it has lower saturation (i.e. closer to gray) than other dark colors
</li><li><b>
tsugi-theme-light-background</b> - This is the overall page background for light mode. It is the lightest theme color and it is OK for this to be #FFFFFF or a very light gray
</li>
</ul>
</p><p>
It is required that the WCAG Luminosity Contrast Ratio between “tsugi-theme-dark” and “tsugi-theme-light” of at least 7.0.  And since the rest of the light and dark colors must be closer to #FFFFFF and #000000 respectively they will have higher contrast rations.
</p><p>
If the provided theme colors do not meet the WCAG Luminosity Contrast Ratio rules, the colors provided will be “nudged” into compliance by adjusting lightness and saturation while maintaining the hue of the provided colors:
</p><p>
<a href="https://en.wikipedia.org/wiki/HSL_and_HSV" target="_blank">
https://en.wikipedia.org/wiki/HSL_and_HSV</a>
</p><p>
It is important to note that accessibility contrast ratio is not the same as “lightness” in the HSL color representation (https://en.wikipedia.org/wiki/HSL_and_HSV) - the color adjustment adjusts for accessibility by tuning the saturation and lightness of the provided colors and *checking* the accessibility ratio until the accessibility contrast ratio.
</p><p>
The simplest approach to theming is to send “tsugi-theme-dark” and let Tsugi decide on the rest of the colors.  Probably the next most important values to send are:
</p>
<pre>
tsugi-theme-dark-text
tsugi-theme-dark-accent
tsugi-theme-light
</pre>
</p><p>
You can explore the effect of choosing different colors and computing colors by sending tsugi-theme-dark and letting Tsugi compute colors.
</p><p>
We will publish the color computation algorithms and rules - for now since the mapping between HSL and accessibility contrast an iterative method of test and check is used until we better understand the goals of the color computations.
</p><p>
These changes are being tracked in the following Sakai JIRA:
</p><p>
<a href="https://jira.sakaiproject.org/browse/SAK-42272" target="_blank">
https://jira.sakaiproject.org/browse/SAK-42272</a>
</p><p>
The source code for the test theme site (above) is available at:
</p><p>
<a href="https://github.com/tsugiproject/tsugi-org/tree/master/theme" target="_blank">
https://github.com/tsugiproject/tsugi-org/tree/master/theme</a>
</p><p>
The current code is only a prototype and needs to be refactored before it is suitable to be published and used.  The most difficult problem to solve is to develop a non-iterative formula to go between HSL values and WCAG Luminosity Contrast Ratio.
</p>

</div>
<?php
$OUTPUT->footer();
