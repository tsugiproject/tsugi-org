<!DOCTYPE html>
    <html lang="en">
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tsugi Theme Test</title>
        <!-- Tiny bit of JS -->
        <script src="https://static.tsugi.org/js/tsugiscripts_head.js"></script>
        <!-- Le styles -->
        <link href="https://static.tsugi.org/bootstrap-3.4.1/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://static.tsugi.org/js/jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet">
                <link href="https://static.tsugi.org/fontawesome-free-5.8.2-web/css/all.css" rel="stylesheet">
        <link href="https://static.tsugi.org/fontawesome-free-5.8.2-web/css/v4-shims.css" rel="stylesheet">
        <style>:root {--primary:#0D47A1;--primary-border:#0d4295;--primary-darker:#0c4091;--primary-darkest:#0b3b85;--secondary:#EEEEEE;--text:#111111;--text-light:#5E5E5E;--font-family:sans-serif;--font-size:14px;}</style>
          <link href="https://static.tsugi.org/css/tsugi.css" rel="stylesheet">

<script>var CSRF_TOKEN = "42"; var _TSUGI = {staticroot: "https://static.tsugi.org"}; </script>
</head
<body prefix="oer: http://oerschema.org">
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
      <a class="navbar-brand" href="#">Tsugi Theme Test</a>
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
      <a class="navbar-brand" href="#">Pre/Post</a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
        <li class="active"><a href="#" ><span class="fas fa-edit" aria-hidden="true"></span> Build</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="fas fa-poll-h" aria-hidden="true"></span> Results <span class="fa fa-caret-down" aria-hidden="true"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#" >By Student</a></li>
            <li><a href="#" >By Question</a></li>
          </ul>
        </li>
        <li><a href="#" ><span class="fas fa-user-graduate" aria-hidden="true"></span> Student View</a></li>
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
    <p class="lead">Create a question that students can respond to before and after an activity.</p>
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
<h1>
<a href="#" onclick="$('#tsugi-theme').toggle();$('#ims-theme').toggle();return false" class="btn btn-warning">Switch Theme Approach<a>
</h1>
<div id="tsugi-theme">
<h2>Tsugi</h2><br/>
<span style="color: var(--primary);">
primary
</span>
 <input type="color" id="primary" value="#0D47A1" onchange="updateTsugiColors();"><br/>

<span style="color: var(--primary-border);">
primary-border
</span>
 <input type="color" id="primary-border" value="#0d4295" onchange="updateTsugiColors();"><br/>

<span style="color: var(--primary-darker);">
primary-darker
</span>
 <input type="color" id="primary-darker" value="#0c4091" onchange="updateTsugiColors();"><br/>

<span style="color: var(--primary-darkest);">
primary-darkest
</span>
 <input type="color" id="primary-darkest" value="#0b3b85" onchange="updateTsugiColors();"><br/>

<span style="color: var(--secondary);">
secondary
</span>
 <input type="color" id="secondary" value="#EEEEEE" onchange="updateTsugiColors();"><br/>

<span style="color: var(--text);">
text
</span>
 <input type="color" id="text" value="#111111" onchange="updateTsugiColors();"><br/>

<span style="color: var(--text-light);">
text-light
</span>
 <input type="color" id="text-light" value="#5E5E5E" onchange="updateTsugiColors();"><br/>
</div>
<div id="ims-theme" style="display:none;">
<h2>IMS</h2><br/>
<span style="color: var(--ims-lti-dark);">
ims-lti-dark
</span>
<input type="color" id="ims-lti-dark" value="#0D47A1" onchange="updateIMSColors();">
<br/>

<span style="color: var(--ims-lti-dark-lighter);">
ims-lti-dark-lighter
</span>
<input type="color" id="ims-lti-dark-lighter" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-dark-compute" checked> Compute
<br/>

<span style="color: var(--ims-lti-dark-darker);">
ims-lti-dark-darker
</span>
<input type="color" id="ims-lti-dark-darker" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-dark-compute" checked> Compute
<br/>

<span style="color: var(--ims-lti-dark-accent);">
ims-lti-dark-accent
</span>
<input type="color" id="ims-lti-dark-accent" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-dark-compute" checked> Compute
<br/>

<span style="color: var(--ims-lti-light);">
ims-lti-light
</span>
<input type="color" id="ims-lti-light" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-light-compute" checked> Compute
<br/>

<span style="color: var(--ims-lti-light-lighter);">
ims-lti-light-lighter
</span>
<input type="color" id="ims-lti-light-lighter" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-light-lighter-compute" checked> Compute
<br/>

<span style="color: var(--ims-lti-light-darker);">
ims-lti-light-darker
</span>
<input type="color" id="ims-lti-light-darker" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-light-darker-compute" checked> Compute
<br/>

<span style="color: var(--ims-lti-light-accent);">
ims-lti-light-accent
</span>
<input type="color" id="ims-lti-light-accent" value="#0D47A1" onchange="updateIMSColors();">
<input type="checkbox" name="ims-lti-light-accent-compute" checked> Compute
<br/>

</div>
<br clear="all"/>
<script>
function updateTsugiColors() {
    var cssnames = ['primary', 'primary-border', 'primary-darker', 'primary-darkest', 'secondary', 'text', 'text-light'];
    for(var i=0; i < cssnames.length; i++) {
        cssname = cssnames[i];
        console.log(cssname);
        var value = $("#"+cssname).val();
        document.documentElement.style.setProperty('--'+cssname, value);
    }
}
function updateIMSColors() {
    var cssnames = [
        'ims-lti-dark',
        'ims-lti-dark-lighter',
        'ims-lti-dark-darker',
        'ims-lti-dark-accent',
        'ims-lti-light',
        'ims-lti-light-lighter',
        'ims-lti-light-darker',
        'ims-lti-light-accent'
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
                <h4 class="modal-title">Pre/Post Reflection Help</h4>
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

<script>
$(document).ready(function () {
    updateTsugiColors();
});
</script>

</div></body>
</html>
