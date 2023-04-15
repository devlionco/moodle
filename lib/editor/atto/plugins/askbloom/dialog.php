<?php
/**
 *
 * AskBloom HTMLAREA custom plugin is heavily based on Ian Byrd's work: "The Differentiator"
 * Ian's email: ian@byrdseed.com (http://www.byrdseed.com/about/)
 * Ian's website: http://www.byrdseed.com/
 *
 * The Differentiator: http://www.byrdseed.com/the-differentiator/
 * All right reserve to Ian Byrd
 *
 * Around Aug-2010...
 * It was adapted to work with the Moodle framework (By Nadav Kavalerchik, nadavkav@gmail.com)
 * Translation infrastructure was added + English and Hebrew translation.
 * and the necessary wrappers to make it plug into HTMLAREA editor.
 *
 * Enjoy :-)
 **/

require_once(__DIR__ . '/../../../../../config.php');

$id = optional_param('id', '', PARAM_TEXT);
$type = optional_param('type', '', PARAM_TEXT);

if (isset($type) && $type == 'atto') {
    $id = preg_replace('/[^-a-zA-Z0-9_]/', '', $id);
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
        <title><?php echo get_string('windowtitle', 'atto_askbloom'); ?></title>
        <link rel="stylesheet" type="text/css" href="TheDifferentiator/jqueryui.css">

        <style type="text/css">
            body {
                direction: ltr;
                margin: 5px;
                padding: 5px;
                font: 1em "Trebuchet MS", verdana, arial, sans-serif;
                font-size: 100%;
            }

            h2 {
                text-align: center;
                font-size: 1em;
                color: #333;
                font-weight: normal;
                margin: 0;
                background: #eee;
                border: 1px solid #aaa;
                padding: 2px;
            }

            h3 {
                font-size: 1em;
                color: #333;
                margin: 0;
            }

            #objective {
                color: #333;
                padding: 10px 0;
                margin: 15px 0;
                border: 1px solid #ccc;
                background: #eee;
            }

            .innerTabs {
                color: #666;
                font-size: .9em
                line-height: 1.2em;
            }

            .innerTabs li ul li {
                border: 1px solid #ccc;
                padding: 10px 5px;
                margin: 5px 0;
            }

            ul {
                list-style: none;
                padding: 0;
            }

            div#tabs-1 ul li.innerTab {
                width: 15%;
            }

            div#tabs-2 ul li.innerTab {
                width: 23%;
            }

            div#tabs-3 ul li.innerTab {
                width: 23%;
            }

            div#tabs-4 ul li.innerTab {
                width: 18%;
            }

            div#tabs-5 ul li.innerTab {
                width: 50%;
            }

            .innerTab {
                float: left;
                padding: 0 5px;
                padding-bottom: 0pt;
            }

            .drawer-content UL {
                float: none;
                padding-top: 7px;
            }

            .drawer-content LI A {
                display: block;
                overflow: hidden;
            }

            li.hover {
                color: #000;
                background: #fdd;
                border: 1px solid #333;
            }

            .editable {
                border-bottom: 1px dashed #000;
            }

            h1 {
                text-align: center;
            }

            #objective {
                text-align: left;
            }

            #objective a {
                margin-right: 5px;
                border: 1px solid green;
                padding: 5px;
                color: green;
                text-decoration: none;
                background: #afa;
            }

            div.bottom {
                clear: both;
            }

            p {
                font-size: .7em;
                padding: 0;
                margin: 0;
                color: #777;
            }

            .ui-tabs .ui-tabs-nav li {
                float: left;
            }

            .ui-widget {
                font-size: 0.8em
            }

            <?php
            if (right_to_left()) {
            ?>
            body {
                direction: rtl;
            }

            .innerTab {
                float: right;
            }

            #objective {
                text-align: right;
            }

            .ui-tabs .ui-tabs-nav li {
                float: right;
            }

            <?php
            }
            ?>

        </style>

        <script type="text/javascript">
            function Init() {
                document.getElementById('objective').focus();
            }

            function onOK() {
                var result = document.getElementById("obj").innerText;
                window.parent.postMessage({"result": result, "id": "<?= $id?>"}, '*');

                return false;
            }

            function onCancel() {
                window.close();
                return false;
            }
        </script>

        <script src="TheDifferentiator/jsapi.js" type="text/javascript"></script>
        <script type="text/javascript">
            google.load("jquery", "1.3.2");
            google.load("jqueryui", "1.7.2");
        </script>

        <script src="TheDifferentiator/jquery_002.js" type="text/javascript"></script>
        <script src="TheDifferentiator/jquery-ui.js" type="text/javascript"></script>
        <script src="TheDifferentiator/jquery_004.js" type="text/javascript"></script>
        <script src="TheDifferentiator/jquery_005.js" type="text/javascript"></script>
        <script src="TheDifferentiator/jquery_003.js" type="text/javascript"></script>
        <script src="TheDifferentiator/jquery.js" type="text/javascript"></script>

        <script type="text/javascript">

            google.setOnLoadCallback(function () {

                $("#tabs").tabs();
                $(".editable").editInPlace({
                    callback: function () {
                        return true
                    }
                });
                $('ul#ts li ul li').click(function () {
                    $('#thinking_skill').html($(this).html().toLowerCase());
                    $('#thinking_skill').effect("highlight", {}, 1500);
                });
                $('ul#r li ul li').click(function () {
                    $('#resource').html("<?php echo get_string('jquse', 'atto_askbloom'); ?>" + $(this).html().toLowerCase() + " ");
                    $('#resource').effect("highlight", {}, 1500);
                });
                $('ul#c li ul li').click(function () {
                    $('#content').html("<?php echo get_string('jqto', 'atto_askbloom'); ?>" + $(this).html().toLowerCase() + "<?php echo get_string('jqsubject', 'atto_askbloom'); ?>");
                    $('#content').effect("highlight", {}, 1500);
                });
                $('ul#p li ul li').click(function () {
                    $('#product').html("<?php echo get_string('jqtocreate', 'atto_askbloom'); ?>" + $(this).html().toLowerCase());
                    $('#product').effect("highlight", {}, 1500);
                });
                $('ul#g li ul li').click(function () {
                    $('#groups').html("<?php echo get_string('jqworkinggroupsof', 'atto_askbloom'); ?>" + $(this).html().toLowerCase() + ".");
                    $('#groups').effect("highlight", {}, 1500);
                });

                $('li.innerTab ul li').hover(
                    function () {
                        $(this).addClass("hover");
                    },
                    function () {
                        $(this).removeClass("hover")
                    }
                );
            });

        </script>
    </head>

    <body id="page" onload="Init()">

    <div id="objective">
        <h1 id="obj"><?php echo get_string('dearstudents', 'atto_askbloom'); ?><span style="" id="thinking_skill"></span><span style="" id="content"></span>
            <input type="text" name="objhidden" id="objhidden" value="" style="display: none;">
            <span style="background:none repeat scroll 0 0 transparent;" class="editable" id="your_content">
    <?php echo get_string('clicktoentersubject', 'atto_askbloom'); ?></span>
            <span id="resource"></span><span id="product"></span><span id="groups"></span></h1>
    </div>

    <button type="button" name="ok" onclick="onOK()" id="askbloom_dialog"><?php echo get_string('okimdone', 'atto_askbloom'); ?></button>

    <div class="ui-tabs ui-widget ui-widget-content ui-corner-all" id="tabs">

        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-corner-top ui-tabs-selected ui-state-active ui-state-focus"><a href="#tabs-1"><span
                            title="<?php echo get_string('tabthinkingskillshelp', 'atto_askbloom'); ?>"><?php echo get_string('tabthinkingskills', 'atto_askbloom'); ?></span></a>
            </li>
            <li class="ui-corner-top ui-state-default"><a href="#tabs-2"><span
                            title="<?php echo get_string('tabcontenthelp', 'atto_askbloom'); ?>"><?php echo get_string('tabcontent', 'atto_askbloom'); ?></span></a></li>
            <li class="ui-corner-top ui-state-default"><a href="#tabs-3"><span
                            title="<?php echo get_string('tabresourceshelp', 'atto_askbloom'); ?>"><?php echo get_string('tabresources', 'atto_askbloom'); ?></span></a></li>
            <li class="ui-corner-top ui-state-default"><a href="#tabs-4"><span
                            title="<?php echo get_string('tabproducthelp', 'atto_askbloom'); ?>"><?php echo get_string('tabproduct', 'atto_askbloom'); ?></span></a></li>
            <li class="ui-corner-top ui-state-default"><a href="#tabs-5"><span
                            title="<?php echo get_string('tabgroupshelp', 'atto_askbloom'); ?>"><?php echo get_string('tabgroups', 'atto_askbloom'); ?></span></a></li>
        </ul>

        <div class="ui-tabs-panel ui-widget-content ui-corner-bottom" id="tabs-1">
            <?php echo get_string('taboneinstructions', 'atto_askbloom'); ?>
            <ul id="ts" class="innerTabs">
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleremembering_en', 'atto_askbloom'); ?>"><?php echo get_string('titleremembering', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('rememberhelp', 'atto_askbloom'); ?>"><?php echo get_string('remember', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('listhelp', 'atto_askbloom'); ?>"><?php echo get_string('list', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('definehelp', 'atto_askbloom'); ?>"><?php echo get_string('define', 'atto_askbloom'); ?></span></li>
                        <li class="hover"><span title="<?php echo get_string('statehelp', 'atto_askbloom'); ?>"><?php echo get_string('state', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('repeathelp', 'atto_askbloom'); ?>"><?php echo get_string('repeat', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('duplicatehelp', 'atto_askbloom'); ?>"><?php echo get_string('duplicate', 'atto_askbloom'); ?></span>
                        </li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleunderstanding_en', 'atto_askbloom'); ?>"><?php echo get_string('titleunderstanding', 'atto_askbloom'); ?></span>
                    </h2>
                    <ul>
                        <li><span title="<?php echo get_string('classifyhelp', 'atto_askbloom'); ?>"><?php echo get_string('classify', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('describehelp', 'atto_askbloom'); ?>"><?php echo get_string('describe', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('discusshelp', 'atto_askbloom'); ?>"><?php echo get_string('discuss', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('explainhelp', 'atto_askbloom'); ?>"><?php echo get_string('explain', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('identifyhelp', 'atto_askbloom'); ?>"><?php echo get_string('identify', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('locatehelp', 'atto_askbloom'); ?>"><?php echo get_string('locate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('recognizehelp', 'atto_askbloom'); ?>"><?php echo get_string('recognize', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('reporthelp', 'atto_askbloom'); ?>"><?php echo get_string('report', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('selecthelp', 'atto_askbloom'); ?>"><?php echo get_string('select', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('translatehelp', 'atto_askbloom'); ?>"><?php echo get_string('translate', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('paraphrasehelp', 'atto_askbloom'); ?>"><?php echo get_string('paraphrase', 'atto_askbloom'); ?></span>
                        </li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleapplyhelp', 'atto_askbloom'); ?>"><?php echo get_string('titleapply', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('choosehelp', 'atto_askbloom'); ?>"><?php echo get_string('choose', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('demonstratehelp', 'atto_askbloom'); ?>"><?php echo get_string('demonstrate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('employhelp', 'atto_askbloom'); ?>"><?php echo get_string('employ', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('illustratehelp', 'atto_askbloom'); ?>"><?php echo get_string('illustrate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('interprethelp', 'atto_askbloom'); ?>"><?php echo get_string('interpret', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('operatehelp', 'atto_askbloom'); ?>"><?php echo get_string('operate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('sketchhelp', 'atto_askbloom'); ?>"><?php echo get_string('sketch', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('solvehelp', 'atto_askbloom'); ?>"><?php echo get_string('solve', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('usehelp', 'atto_askbloom'); ?>"><?php echo get_string('use', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('schedulehelp', 'atto_askbloom'); ?>"><?php echo get_string('schedule', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleanalyzehelp', 'atto_askbloom'); ?>"><?php echo get_string('titleanalyze', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('apprisehelp', 'atto_askbloom'); ?>"><?php echo get_string('apprise', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('comparehelp', 'atto_askbloom'); ?>"><?php echo get_string('compare', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('contrasthelp', 'atto_askbloom'); ?>"><?php echo get_string('contrast', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('criticizehelp', 'atto_askbloom'); ?>"><?php echo get_string('criticize', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('differentiatehelp', 'atto_askbloom'); ?>"><?php echo get_string('differentiate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('discriminatehelp', 'atto_askbloom'); ?>"><?php echo get_string('discriminate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('distinguishhelp', 'atto_askbloom'); ?>"><?php echo get_string('distinguish', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('examinehelp', 'atto_askbloom'); ?>"><?php echo get_string('examine', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('experimenthelp', 'atto_askbloom'); ?>"><?php echo get_string('experiment', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('questionhelp', 'atto_askbloom'); ?>"><?php echo get_string('question', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('testhelp', 'atto_askbloom'); ?>"><?php echo get_string('test', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleevaluatehelp', 'atto_askbloom'); ?>"><?php echo get_string('titleevaluate', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('apprisehelp', 'atto_askbloom'); ?>"><?php echo get_string('apprise', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('arguehelp', 'atto_askbloom'); ?>"><?php echo get_string('argue', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('contrasthelp', 'atto_askbloom'); ?>"><?php echo get_string('contrast', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('defendhelp', 'atto_askbloom'); ?>"><?php echo get_string('defend', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('judgehelp', 'atto_askbloom'); ?>"><?php echo get_string('judge', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('selecthelp', 'atto_askbloom'); ?>"><?php echo get_string('select', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('supporthelp', 'atto_askbloom'); ?>"><?php echo get_string('support', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('valuehelp', 'atto_askbloom'); ?>"><?php echo get_string('value', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('evaluatehelp', 'atto_askbloom'); ?>"><?php echo get_string('evaluate', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlecreatehelp', 'atto_askbloom'); ?>"><?php echo get_string('titlecreate', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('assemblehelp', 'atto_askbloom'); ?>"><?php echo get_string('assemble', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('constructhelp', 'atto_askbloom'); ?>"><?php echo get_string('construct', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('createhelp', 'atto_askbloom'); ?>"><?php echo get_string('create', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('designhelp', 'atto_askbloom'); ?>"><?php echo get_string('design', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('develophelp', 'atto_askbloom'); ?>"><?php echo get_string('develop', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('formulatehelp', 'atto_askbloom'); ?>"><?php echo get_string('formulate', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('writehelp', 'atto_askbloom'); ?>"><?php echo get_string('write', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
            </ul>
            <div class="bottom"></div>
        </div>

        <div class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" id="tabs-2">
            <?php echo get_string('tabtwoinstructions', 'atto_askbloom'); ?>
            <ul id="c" class="innerTabs">
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titledepthhelp', 'atto_askbloom'); ?>"><?php echo get_string('titledepth', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('bigideahelp', 'atto_askbloom'); ?>"><?php echo get_string('bigidea', 'atto_askbloom'); ?></span></li>
                        <li>
                            <span title="<?php echo get_string('unansweredquestionshelp', 'atto_askbloom'); ?>"><?php echo get_string('unansweredquestions', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('ethicshelp', 'atto_askbloom'); ?>"><?php echo get_string('ethics', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('patternshelp', 'atto_askbloom'); ?>"><?php echo get_string('patterns', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('ruleshelp', 'atto_askbloom'); ?>"><?php echo get_string('rules', 'atto_askbloom'); ?></span></li>
                        <li>
                            <span title="<?php echo get_string('languageofthedisciplinehelp', 'atto_askbloom'); ?>"><?php echo get_string('languageofthediscipline', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('essentialdetailshelp', 'atto_askbloom'); ?>"><?php echo get_string('essentialdetails', 'atto_askbloom'); ?></span>
                        </li>
                        <li class="last"><span title="<?php echo get_string('trendshelp', 'atto_askbloom'); ?>"><?php echo get_string('trends', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlecomplexityhelp', 'atto_askbloom'); ?>"><?php echo get_string('titlecomplexity', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('multiplepovhelp', 'atto_askbloom'); ?>"><?php echo get_string('multiplepov', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('changeovertimehelp', 'atto_askbloom'); ?>"><?php echo get_string('changeovertime', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span
                                    title="<?php echo get_string('accrossthedisiplinehelp', 'atto_askbloom'); ?>"><?php echo get_string('accrossthedisipline', 'atto_askbloom'); ?></span>
                        </li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleimperativeshelp', 'atto_askbloom'); ?>"><?php echo get_string('titleimperatives', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('originhelp', 'atto_askbloom'); ?>"><?php echo get_string('origin', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('convergencehelp', 'atto_askbloom'); ?>"><?php echo get_string('convergence', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('parallelshelp', 'atto_askbloom'); ?>"><?php echo get_string('parallels', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('paradoxhelp', 'atto_askbloom'); ?>"><?php echo get_string('paradox', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span
                                    title="<?php echo get_string('contributionhelp', 'atto_askbloom'); ?>"><?php echo get_string('contribution', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
            </ul>
            <div class="bottom"></div>
        </div>

        <div class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" id="tabs-3">
            <?php echo get_string('tabthreeinstructions', 'atto_askbloom'); ?>
            <ul id="r" class="innerTabs">
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlevoicehelp', 'atto_askbloom'); ?>"><?php echo get_string('titlevoice', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('recordinghelp', 'atto_askbloom'); ?>"><?php echo get_string('recording', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('musiccdhelp', 'atto_askbloom'); ?>"><?php echo get_string('musiccd', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('tvshowhelp', 'atto_askbloom'); ?>"><?php echo get_string('tvshow', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('interviewhelp', 'atto_askbloom'); ?>"><?php echo get_string('interview', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('radioshowhelp', 'atto_askbloom'); ?>"><?php echo get_string('radioshow', 'atto_askbloom'); ?></span>
                        </li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlenotdigitalhelp', 'atto_askbloom'); ?>"><?php echo get_string('titlenotdigital', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('bookhelp', 'atto_askbloom'); ?>"><?php echo get_string('book', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('magazinehelp', 'atto_askbloom'); ?>"><?php echo get_string('magazine', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('articlehelp', 'atto_askbloom'); ?>"><?php echo get_string('article', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('newspaperhelp', 'atto_askbloom'); ?>"><?php echo get_string('newspaper', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span
                                    title="<?php echo get_string('encyclopediahelp', 'atto_askbloom'); ?>"><?php echo get_string('encyclopedia', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titledigitalhelp', 'atto_askbloom'); ?>"><?php echo get_string('titledigital', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('websitehelp', 'atto_askbloom'); ?>"><?php echo get_string('website', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('wikipediahelp', 'atto_askbloom'); ?>"><?php echo get_string('wikipedia', 'atto_askbloom'); ?></span></li>
                        <li>
                            <span title="<?php echo get_string('onlineencyclopediahelp', 'atto_askbloom'); ?>"><?php echo get_string('onlineencyclopedia', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('bloghelp', 'atto_askbloom'); ?>"><?php echo get_string('blog', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('facebookhelp', 'atto_askbloom'); ?>"><?php echo get_string('facebook', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('twitterhelp', 'atto_askbloom'); ?>"><?php echo get_string('twitter', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span
                                    title="<?php echo get_string('onlinearticlehelp', 'atto_askbloom'); ?>"><?php echo get_string('onlinearticle', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
            </ul>
            <div class="bottom"></div>
        </div>

        <div class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" id="tabs-4">
            <?php echo get_string('tabfourinstructions', 'atto_askbloom'); ?>
            <ul id="p" class="innerTabs">
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlevisualhelp', 'atto_askbloom'); ?>"><?php echo get_string('titlevisual', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('charthelp', 'atto_askbloom'); ?>"><?php echo get_string('chart', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('drawinghelp', 'atto_askbloom'); ?>"><?php echo get_string('drawing', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('timelinehelp', 'atto_askbloom'); ?>"><?php echo get_string('timeline', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('diagramhelp', 'atto_askbloom'); ?>"><?php echo get_string('diagram', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('graphicorganizerhelp', 'atto_askbloom'); ?>"><?php echo get_string('graphicorganizer', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('maphelp', 'atto_askbloom'); ?>"><?php echo get_string('map', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('comichelp', 'atto_askbloom'); ?>"><?php echo get_string('comic', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('bookcoverhelp', 'atto_askbloom'); ?>"><?php echo get_string('bookcover', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('posterhelp', 'atto_askbloom'); ?>"><?php echo get_string('poster', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleconstructhelp', 'atto_askbloom'); ?>"><?php echo get_string('titleconstruct', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('modelhelp', 'atto_askbloom'); ?>"><?php echo get_string('model', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('sculpturehelp', 'atto_askbloom'); ?>"><?php echo get_string('sculpture', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('dioramahelp', 'atto_askbloom'); ?>"><?php echo get_string('diorama', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('miniaturehelp', 'atto_askbloom'); ?>"><?php echo get_string('miniature', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('artgalleryhelp', 'atto_askbloom'); ?>"><?php echo get_string('artgallery', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('museumexhibithelp', 'atto_askbloom'); ?>"><?php echo get_string('museumexhibit', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('mobilehelp', 'atto_askbloom'); ?>"><?php echo get_string('mobile', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('collagehelp', 'atto_askbloom'); ?>"><?php echo get_string('collage', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('mosaichelp', 'atto_askbloom'); ?>"><?php echo get_string('mosaic', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titleoralhelp', 'atto_askbloom'); ?>"><?php echo get_string('titleoral', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('debatehelp', 'atto_askbloom'); ?>"><?php echo get_string('debate', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('paneldiscussionhelp', 'atto_askbloom'); ?>"><?php echo get_string('paneldiscussion', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('lessonhelp', 'atto_askbloom'); ?>"><?php echo get_string('lesson', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('reporthelp', 'atto_askbloom'); ?>"><?php echo get_string('report', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('playhelp', 'atto_askbloom'); ?>"><?php echo get_string('play', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('readerstheatrehelp', 'atto_askbloom'); ?>"><?php echo get_string('readerstheatre', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('pressconferencehelp', 'atto_askbloom'); ?>"><?php echo get_string('pressconference', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('talkshowhelp', 'atto_askbloom'); ?>"><?php echo get_string('talkshow', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('monologuehelp', 'atto_askbloom'); ?>"><?php echo get_string('monologue', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span
                                    title="<?php echo get_string('siskelroperreviewhelp', 'atto_askbloom'); ?>"><?php echo get_string('siskelroperreview', 'atto_askbloom'); ?></span>
                        </li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlemultimediahelp', 'atto_askbloom'); ?>"><?php echo get_string('titlemultimedia', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('songhelp', 'atto_askbloom'); ?>"><?php echo get_string('song', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('illustratedbookhelp', 'atto_askbloom'); ?>"><?php echo get_string('illustratedbook', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('newspaperhelp', 'atto_askbloom'); ?>"><?php echo get_string('newspaper', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('tvshowhelp', 'atto_askbloom'); ?>"><?php echo get_string('tvshow', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('presentationhelp', 'atto_askbloom'); ?>"><?php echo get_string('presentation', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('videopoetryhelp', 'atto_askbloom'); ?>"><?php echo get_string('videopoetry', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('photoessayhelp', 'atto_askbloom'); ?>"><?php echo get_string('photoessay', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('videotraveloguehelp', 'atto_askbloom'); ?>"><?php echo get_string('videotravelogue', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('newsreporthelp', 'atto_askbloom'); ?>"><?php echo get_string('newsreport', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('webpagehelp', 'atto_askbloom'); ?>"><?php echo get_string('webpage', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
                <li class="innerTab">
                    <h2><span title="<?php echo get_string('titlewritenhelp', 'atto_askbloom'); ?>"><?php echo get_string('titlewriten', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li>
                            <span title="<?php echo get_string('responsetolitreturehelp', 'atto_askbloom'); ?>"><?php echo get_string('responsetolitreture', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('reporthelp', 'atto_askbloom'); ?>"><?php echo get_string('report', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('articlehelp', 'atto_askbloom'); ?>"><?php echo get_string('article', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('persuasiveessayhelp', 'atto_askbloom'); ?>"><?php echo get_string('persuasiveessay', 'atto_askbloom'); ?></span>
                        </li>
                        <li><span title="<?php echo get_string('sequelhelp', 'atto_askbloom'); ?>"><?php echo get_string('sequel', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('letterhelp', 'atto_askbloom'); ?>"><?php echo get_string('letter', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('childrenstoryhelp', 'atto_askbloom'); ?>"><?php echo get_string('childrenstory', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('poemsonghelp', 'atto_askbloom'); ?>"><?php echo get_string('poemsong', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('eulogyhelp', 'atto_askbloom'); ?>"><?php echo get_string('eulogy', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('diaryhelp', 'atto_askbloom'); ?>"><?php echo get_string('diary', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('reviewhelp', 'atto_askbloom'); ?>"><?php echo get_string('review', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span
                                    title="<?php echo get_string('storyinanewgenrehelp', 'atto_askbloom'); ?>"><?php echo get_string('storyinanewgenre', 'atto_askbloom'); ?></span>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="bottom"></div>
        </div>

        <div class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide" id="tabs-5">
            <ul id="g" class="innerTabs">
                <li class="innerTab">
                    <?php echo get_string('tabfiveinstructions', 'atto_askbloom'); ?>
                    <h2><span title="<?php echo get_string('titlegroupsofhelp', 'atto_askbloom'); ?>"><?php echo get_string('titlegroupsof', 'atto_askbloom'); ?></span></h2>
                    <ul>
                        <li><span title="<?php echo get_string('onehelp', 'atto_askbloom'); ?>"><?php echo get_string('one', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('twohelp', 'atto_askbloom'); ?>"><?php echo get_string('two', 'atto_askbloom'); ?></span></li>
                        <li><span title="<?php echo get_string('threehelp', 'atto_askbloom'); ?>"><?php echo get_string('three', 'atto_askbloom'); ?></span></li>
                        <li class="last"><span title="<?php echo get_string('fourhelp', 'atto_askbloom'); ?>"><?php echo get_string('four', 'atto_askbloom'); ?></span></li>
                    </ul>
                </li>
            </ul>
            <div class="bottom"></div>
        </div>
    </div> <!--End Of Tabs-->

    <div style="color:gray;">AskBloom HTMLAREA custom plugin is heavily based on Ian Byrd's work: "<a href="http://www.byrdseed.com/the-differentiator/">The Differentiator</a>"
    </div>
    </body>
    </html>
    <?php
} else {
    return false;
}