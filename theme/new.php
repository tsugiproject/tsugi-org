<?php
require_once("Color.php");
require_once("rgbToHSL.php");
require_once("HSLuv.php");
require_once("tsugi_color_util.php");
?>
<!DOCTYPE html>
    <html lang="en">
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tsugi New Theme Test</title>
        <!-- Tiny bit of JS -->
        <script src="https://static.tsugi.org/js/tsugiscripts_head.js"></script>
        <!-- Le styles -->
        <link href="https://static.tsugi.org/bootstrap-3.4.1/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://static.tsugi.org/js/jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet">
                <link href="https://static.tsugi.org/fontawesome-free-5.8.2-web/css/all.css" rel="stylesheet">
        <link href="https://static.tsugi.org/fontawesome-free-5.8.2-web/css/v4-shims.css" rel="stylesheet">
        <style>:root {--background-color: #FFFFFF; --primary:#0D47A1;--primary-border:#0d4295;--primary-darker:#0c4091;--primary-darkest:#0b3b85;--secondary:#EEEEEE;--text:#111111;--text-light:#5E5E5E;--font-family:sans-serif;--font-size:14px;}</style>
          <link href="https://static.tsugi.org/css/tsugi.css" rel="stylesheet">

<script>var CSRF_TOKEN = "42"; var _TSUGI = {staticroot: "https://static.tsugi.org"}; </script>
</head
<body prefix="oer: http://oerschema.org" style="color: var(--text); background-color: var(--background-color);">
<div id="body_container">
<script>
document.getElementById("body_container").className = "container";
</script>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation" id="tsugi_main_nav_bar" style="display:none">  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Tsugi New Theme Test</a>
    </div>
    <div class="navbar-collapse collapse">
    </div> <!--/.nav-collapse -->
  </div> <!--container -->
</nav>
<nav class="navbar navbar-default" role="navigation" id="tsugi_tool_nav_bar">  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">New</a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
        <li class="active"><a href="contrast.php" target="_blank"><span class="fas fa-edit" aria-hidden="true"></span> Contrast Checker</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="fas fa-poll-h" aria-hidden="true"></span> Themes <span class="fa fa-caret-down" aria-hidden="true"></span></a>
          <ul class="dropdown-menu">
            <li><a href="classic.php" >Classic</a></li>
            <li><a href="index.php" >Transition</a></li>
            <li><a href="new.php" >New</a></li>
          </ul>
        </li>
        <li><a href="#"><span class="fas fa-user-graduate" aria-hidden="true"></span> TBD</a></li>
      </ul>
    </div> <!--/.nav-collapse -->
  </div> <!--container -->
</nav>
<script>
if ( ! inIframe() ) {
  document.getElementById('tsugi_main_nav_bar').style.display = 'block';
  document.getElementsByTagName('body')[0].style.paddingTop = '5.93rem';
} else {
  document.getElementById('tsugi_tool_nav_bar').classList.add("navbar-fixed-top");
  document.getElementsByTagName('body')[0].style.paddingTop = '5.93rem';
}
</script>
<div class="container-fluid"><div id="flashmessages"></div>    <div id="toolTitle" class="h1">
        <button id="helpButton" type="button" class="btn btn-link pull-right" data-toggle="modal" data-target="#helpModal"><span class="fa fa-question-circle" aria-hidden="true"></span> Help</button>
        <span class="flx-cntnr flx-row flx-nowrap flx-start">
            <span class="title-text-span" onclick="PrePostJS.editTitleText();" tabindex="0">Pre/Post Reflection</span>
            <a id="toolTitleEditLink" class="toolTitleAction" href="#">
                <span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
                <span class="sr-only">Edit Title Text</span>
            </a>
        </span>
    </div>
    <form id="toolTitleForm" action="actions/UpdateMainTitle.php" method="post" style="display:none;"><input type="hidden" name="PHPSESSID" value="9ea846b475ac4cff2470971d56a211aa" />
        <label for="toolTitleInput" class="sr-only">Title Text</label>
        <div class="h1 flx-cntnr flx-row flx-nowrap flx-start">
            <textarea class="title-edit-input flx-grow-all" id="toolTitleInput" name="toolTitle" rows="2">Pre/Post Reflection</textarea>
            <a id="toolTitleSaveLink" class="toolTitleAction" href="#">
                <span class="fa fa-fw fa-save" aria-hidden="true"></span>
                <span class="sr-only">Save Title Text</span>
            </a>
            <a id="toolTitleCancelLink" class="toolTitleAction" href="#">
                <span class="fa fa-fw fa-times" aria-hidden="true"></span>
                <span class="sr-only">Cancel Title Text</span>
            </a>
        </div>
    </form>
    <p class="lead">This will the new theme values, mapped to new markup, hopefully with a functionioning dark mode.
 This is currently incomplete and just a copy of the transition theme.
</p>
    <section id="theQuestions">
        <h2 class="hdr-nobot-mrgn"><small>Pre-Question</small></h2>
        <div id="preQuestionRow" class="h3 inline hdr-notop-mrgn flx-cntnr flx-row flx-nowrap flx-start question-row">
            <div class="flx-grow-all question-text">
                <span class="question-text-span" onclick="PrePostJS.editPreQuestionText();" id="questionTextPre" tabindex="0">What is a greatest common factor?</span>
                <form id="preQuestionTextForm" action="actions/UpdatePreQuestion.php" method="post" style="display:none;"><input type="hidden" name="PHPSESSID" value="9ea846b475ac4cff2470971d56a211aa" />
                    <label for="preQuestionTextInput" class="sr-only">Pre-Question Text</label>
                    <textarea class="form-control" id="preQuestionTextInput" name="questionText" rows="2" required>What is a greatest common factor?</textarea>
                </form>
            </div>
            <a id="preQuestionEditAction" href="#">
                <span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
                <span class="sr-only">Edit Pre-Question Text</span>
            </a>
            <a id="preQuestionSaveAction" href="#" style="display:none;">
                <span aria-hidden="true" class="fa fa-fw fa-save"></span>
                <span class="sr-only">Save Pre-Question</span>
            </a>
            <a id="preQuestionCancelAction" href="#" style="display: none;">
                <span aria-hidden="true" class="fa fa-fw fa-times"></span>
                <span class="sr-only">Cancel Pre-Question</span>
            </a>
        </div>
</section>
<section>
<?php 
$tsugi_dark = '#000000';
if ( isset($_REQUEST['color']) ) {
    $tsugi_dark = $_REQUEST['color'];
}
$fromwhite = Color::relativeLuminance("#FFFFFF", $tsugi_dark);
if ( $fromwhite < 8.0 ) {
    $mid = findLMidPointForHue($tsugi_dark);
} else {
    $rgb = fixRgb($tsugi_dark);
    $mid = findLMidPointForHue($rgb[0], $rgb[1], $rgb[2], $tsugi_dark);
}

$outerpair = luminosityPair(20.0, $mid);
$innerpair = luminosityPair(7.0, $mid);

?>
</div>
<div id="tsugi-theme" style="display:block;">
<form>
Choose dark color: <input type="color" name="color" value="<?= $tsugi_dark ?>">
Contrast from white: <?= $fromwhite ?> 
<br/>
<input type="submit" value="Populate Colors"></br>
Outer dark: <input type="color" value="<?= $outerpair[0] ?>" readonly>
Inner dark: <input type="color" value="<?= $innerpair[0] ?>" readonly>
Hue midpoint: <input type="color" value="<?= $mid ?>" readonly>
Inner light: <input type="color" value="<?= $innerpair[1] ?>" readonly>
Outer light: <input type="color" value="<?= $outerpair[1] ?>" readonly>
</form>
<hr/>
<?php 
$dark_outer_hsl = rgbToHsl($outerpair[0]);
$dark_inner_hsl = rgbToHsl($innerpair[0]);

$light_inner_hsl = rgbToHsl($innerpair[1]);
$light_outer_hsl = rgbToHsl($outerpair[1]);

$tsugi_dark_hsl = rgbToHsl($tsugi_dark);

$ddelta = $dark_inner_hsl[2] - $dark_outer_hsl[2];
$ldelta = $light_outer_hsl[2] - $light_inner_hsl[2];

$hue = $dark_outer_hsl[0];
$sat_dark = $dark_outer_hsl[1];
$sat_light = $light_outer_hsl[1];
$lightness_dark = $dark_outer_hsl[2];
$lightness_light = $light_outer_hsl[2];

if ( $fromwhite < 8.0 ) {
    $tsugi_dark = Color::hex(hslToRgb($hue, $sat_dark, $lightness_dark + ($ddelta * 0.6)));
    $tsugi_dark_hsl = rgbToHsl($tsugi_dark);
}

$lightness_darker = ($tsugi_dark_hsl[2] + $dark_outer_hsl[2]) / 2.0;
$lightness_dark_accent = ($tsugi_dark_hsl[2] + $dark_inner_hsl[2]) / 2.0;

$tsuginames = array( 
    "tsugi-dark-background" => $outerpair[0],
    "tsugi-dark-text" =>  Color::hex(hslToRgb($hue, $sat_dark*0.5, $lightness_darker)),
    "tsugi-dark-darker" => Color::hex(hslToRgb($hue, $sat_dark, $lightness_darker)),
    "tsugi-dark" =>  $tsugi_dark,
    "tsugi-dark-accent" => Color::hex(hslToRgb($hue, $sat_dark, $lightness_dark_accent)),
    "tsugi-light-accent" => $innerpair[1],
    "tsugi-light" => Color::hex(hslToRgb($hue, $sat_light, $lightness_light - ($ldelta * 0.6))),
    "tsugi-light-lighter" => Color::hex(hslToRgb($hue, $sat_light, $lightness_light - ($ldelta * 0.3))),
    "tsugi-light-text" => Color::hex(hslToRgb($hue, $sat_light*0.5, $lightness_light - ($ldelta * 0.3))),
    "tsugi-light-background" => $outerpair[1],
);


echo("<script>\n var tsuginames = [\n");
foreach($tsuginames as $name => $default) {
    echo("'$name', ");
}
echo("];\n</script>\n");

$tusgitolegacy = array(
   'tsugi-dark-lighter' => ['primary', 'text-light'],
   'tsugi-dark-text' => ['text', 'primary-darkest'],
   'tsugi-dark' => ['primary', 'text-light'],
   'tsugi-dark-darker' => 'primary-darker', 
   'tsugi-dark-accent' => 'primary-border', 
   'tsugi-light-darker' => 'secondary',
   'tsugi-light-background' => 'background-color',
);

echo("<script>\n var tusgitolegacy = \n");
echo(json_encode($tusgitolegacy));
echo(";\n");
echo("console.log(tusgitolegacy);\n");
echo("</script>\n");

foreach($tsuginames as $name => $default) {
    $template = <<< EOT
<span style="color: var(--$name);">
$name
</span>
<input type="color" id="$name" value="$default" onchange="updateColors(tsuginames);">

EOT;
    echo($template);
    $legacy = isset($tusgitolegacy[$name]) ? $tusgitolegacy[$name] : false;
    if ( $legacy ) {
        if ( is_string($legacy) ) {
            echo(" ".$legacy);
        } else {
            $legacies = $legacy;
            $z = 0;
            foreach($legacies as $legacy) {
                if ( $z == 1 ) echo(",");
                $z = 1;
                echo(" ".$legacy);
            }
        }
    }
    echo("<br/>\n");
}
echo('<span style="padding: 5px; color: '.$tsuginames['tsugi-light-text'].'; background-color: '.$tsuginames['tsugi-dark'].';">tsugi-light-text on tsugi-dark</span> ');
echo('<span style="padding: 5px; color: '.$tsuginames['tsugi-dark-text'].'; background-color: '.$tsuginames['tsugi-light-background'].';">tsugi-dark-text on tsugi-light-background</span> ');
echo('<span style="padding: 5px; color: '.$tsuginames['tsugi-light-text'].'; background-color: '.$tsuginames['tsugi-dark-background'].';">tsugi-light-text on tsugi-dark-background (dark mode)</span> ');
?>

<div id="tsugi-values" style="position: relative; border: black 2px solid; width: 100%; background-image: linear-gradient(to right, white , black);">
<?php
foreach($tsuginames as $name => $default) {
    echo('<span id="'.$name.'-ratio" style="color: var(--'.$name.'); position: absolute; padding: 10px 0;"></span><br/>'."\n");
}
echo("&nbsp;</br/>\n");
?>
</div>
<br clear="all"/>
<script>
function updateColors(cssnames) {
    for(var i=0; i < cssnames.length; i++) {
        cssname = cssnames[i];
        var value = $("#"+cssname).val();
        var lum = relativeLuminance(breakHEX(value));
        var percent = Math.round(lum*100);
        var label = getShortLabel(cssname);
        label = cssname;
        // console.log(cssname, label, value, lum, percent);
        $("#"+cssname+'-ratio').html(label+' '+percent);
        if ( percent < 50 ) {
            $("#"+cssname+'-ratio').css('left', percent+'%');
            $("#"+cssname+'-ratio').css('right', 'initial');
        } else {
            $("#"+cssname+'-ratio').css('right', (100-percent)+'%');
            $("#"+cssname+'-ratio').css('left', 'initial');
        }
        document.documentElement.style.setProperty('--'+cssname, value);
        var tsugi = tusgitolegacy[cssname];
        if ( ! tsugi ) continue;
        console.log('tsugi', tsugi);
        if ( Array.isArray(tsugi) ) {
            for(var j=0; j<tsugi.length; j++) {
                var tsug = tsugi[j];
                console.log(tsug);
                document.documentElement.style.setProperty('--'+tsug, value);
            }
        } else if ( tsugi ) {
            document.documentElement.style.setProperty('--'+tsugi, value);
        }
    }
}
function updateIMSColors() {
    var cssnames = [
        'tsugi-dark',
        'tsugi-dark-lighter',
        'tsugi-dark-darker',
        'tsugi-dark-accent',
        'tsugi-light',
        'tsugi-light-lighter',
        'tsugi-light-darker',
        'tsugi-light-accent'
    ];
    for(var i=0; i < cssnames.length; i++) {
        cssname = cssnames[i];
        console.log(cssname);
        var value = $("#"+cssname).val();
        document.documentElement.style.setProperty('--'+cssname, value);
    }
}
</script>
    </section>
        <input type="hidden" id="sess" value="9ea846b475ac4cff2470971d56a211aa">
<div id="helpModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span class="fa fa-times" aria-hidden="true"></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Tsugi New</h4>
            </div>
            <div class="modal-body">
                                        <h4>General Help</h4>
                        <p>Use this page to add a Pre-Question and Post-Question for your students to answer. You may optionally include an amount of time students must wait before responding to the Post-Question and/or a Wrap-Up Question for students to answer at the end.</p>
                        <p>Students will not be able to see their response to the Pre-Question until they've responded to the Post-Question.</p>
                        <h5>Modifying Pre/Post Settings</h5>
                        <p>Once you've set up the Pre/Post Reflection, click on the pencil icon next to any item you'd like to modify. When you are finished editing the item, click on the save icon or press the 'Enter' key to save your changes.</p>
                        <h5>Editing the Title</h5>
                        <p>You can edit the title of this Pre/Post Reflection by clicking the pencil icon next to the title at the top of this page.</p>
                                    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
<script src="https://static.tsugi.org/js/jquery-1.11.3.js"></script>
<script src="https://static.tsugi.org/bootstrap-3.4.1/js/bootstrap.min.js"></script>
<script src="https://static.tsugi.org/js/jquery-ui-1.11.4/jquery-ui.min.js"></script>
<script src="https://static.tsugi.org/js/jquery.timeago-1.6.3.js"></script>
<script src="https://static.tsugi.org/js/handlebars-v4.0.2.js"></script>
<script src="https://static.tsugi.org/tmpljs-3.8.0/tmpl.min.js"></script>
<script src="https://static.tsugi.org/js/tsugiscripts.js"></script>
<script src="relative-luminance.js"></script>
<script src="tsugi_theme_library.js"></script>
<script src="color-conversion-algorithms.js"></script>

<script>
$(document).ready(function () {
    console.log(relativeLuminance(breakHEX('#000000')),relativeLuminance(breakHEX('#0d47a1')), relativeLuminance(breakHEX('#FFFFFF')));
    var yellow = contrast([255, 255, 255], [255, 255, 0]); // 1.074 for yellow
    var blue = contrast([255, 255, 255], [0, 0, 255]); // 8.592 for blue
    var hslgreen = rgbToHsl(breakHEX('#00FF00'));
    console.log('hslgreen', hslgreen);
    var hsvgreen = rgbToHsv(breakHEX('#00FF00'));
    console.log('hsvgreen', hsvgreen);
    // console.log('contrast white to yellow 1.074', yellow);
    // console.log('contrast white to blue 8.592', blue); 
    updateColors(tsuginames);

});
</script>

</div></body>
</html>
