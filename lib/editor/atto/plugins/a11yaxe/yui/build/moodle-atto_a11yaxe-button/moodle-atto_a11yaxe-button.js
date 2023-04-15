YUI.add('moodle-atto_a11yaxe-button', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_a11yaxe
 * @copyright  2021 Oren-Shmuel, Tamir-Hajaj, Dmitry-Lezinsky (Students from AAC) Under the guidance of Nadav Kavalerchik
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_a11yaxe-button
 */

/**
 * Accessibility Checking tool for the Atto editor.
 *
 * @namespace M.atto_a11yaxe
 * @class Button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENT = 'atto_a11yaxe';
var state = 0; // 0 means dialog close
var dialogStyle; // Css link tag
var contentDiv; // Dialog div
var configPlugin; // Plugin config object.

Y.namespace('M.atto_a11yaxe').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    initializer: function(config) {
        configPlugin = config;

        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_a11yaxe',
            callback: this._displayDialogue
        });
        // Load 3rd party axe-core.js (axe.js) library.
        // https://github.com/dequelabs/axe-core
        Y.Get.js(M.cfg.wwwroot + '/lib/editor/atto/plugins/a11yaxe/amd/src/axe.js', function(err) {
            if (err) {
            }
        });
    },

    _displayDialogue: function(e) {

        require(['jquery', 'jqueryui'], function($) {
            // Debugger; // if you need to fix debugging problem.
            var textbox = $(e.container._node).closest('.editor_atto').find('[role="textbox"]');
            $.getJSON(M.cfg.wwwroot + '/lib/editor/atto/plugins/a11yaxe/amd/src/locales/he.json', function(jsonLangHebrew) {
                // Console.log(json); // this will show the info it in firebug console
                // Init Axe, only after he.json lang file is loaded.
                var configure = {};
                if (configPlugin.checks !== undefined && configPlugin.checks.length > 0) {
                    configure.checks = JSON.parse(configPlugin.checks);
                }

                // Check if UI is Hebrew, and then use he.json, else use English.
                var lang = document.documentElement.lang;
                if (lang === 'he' || lang === 'he_kids') {
                    configure.locale = jsonLangHebrew;
                }

                axe.configure(configure);

                axe.run(
                    textbox, // To work on every atto in every page
                    {
                        runOnly: {
                            type: 'tag',
                            values: ['wcag2a', 'wcag2aa']
                        }
                    },
                    function(err, results) {
                        if (err) {
                            throw err;
                        }
                        var result = results.violations;
                        // Prints for check
                        results.violations.forEach(function(issue) {
                        });
                        if (dialogStyle === undefined) {
                            dialogStyle = document.createElement('link');
                            dialogStyle.setAttribute('rel', 'stylesheet');
                            dialogStyle.setAttribute('href', M.cfg.wwwroot + '/lib/jquery/ui-1.13.0/jquery-ui.css');
                            document.querySelector("head").appendChild(dialogStyle);
                        }
                        if (!state) {
                            contentDiv = document.createElement('div');
                            contentDiv.setAttribute('id', 'result');
                            document.body.appendChild(contentDiv);
                            contentDiv.title = M.util.get_string('there_are', COMPONENT) + " "
                                + results.violations.length + " " + M.util.get_string('problems', COMPONENT);
                        }

                        $(function() {
                            var errorIndex = 0; // Index of issue
                            var errorElement;
                            if (!state) { // If not already
                                $('#result').dialog({
                                    open: function() {
                                        $(".ui-dialog-titlebar-close").hide();
                                        state = 1; // 1 means opened
                                        if (!result[0]) {
                                            $(this).text(M.util.get_string('allgood', COMPONENT));
                                        } else {
                                            $(this).text(errorIndex + 1 + ") " + result[errorIndex].description);
                                        }
                                    },
                                    width: 400,
                                    height: 200,
                                    buttons: [{
                                        text: M.util.get_string('dialog_close', COMPONENT),
                                        click: function() {
                                            state = 0; // 0 means closed
                                            $(this).closest('.ui-dialog-content').dialog('close');
                                            $(this).remove(); // To delete dialog box
                                            // Update latest stats
                                            $.ajax({
                                                method: "POST",
                                                dataType: 'text',
                                                // Cache: true,
                                                url: M.cfg.wwwroot + '/lib/editor/atto/plugins/a11yaxe/ajax_update_stats.php',
                                                data: {stats_count: result.length}
                                            }).fail(function() {
                                                // Error handling
                                            }).done(function() {
                                                // Success msg
                                            });
                                        }}, {
                                        text: M.util.get_string('dialog_previous', COMPONENT),
                                        click: function() {
                                            if (errorIndex > 0) {
                                                errorIndex--; // Decrement issue index
                                                $(this).text(errorIndex + 1 + ") " + result[errorIndex].description);
                                            }
                                        }}, {
                                        text: M.util.get_string('dialog_next', COMPONENT),
                                        click: function() {
                                            if (errorIndex < result.length - 1) {
                                                errorIndex++; // Increment issue index
                                                $(this).text(errorIndex + 1 + ") " + result[errorIndex].description);
                                            }
                                        }}, {
                                        text: M.util.get_string('dialog_showme', COMPONENT),
                                        click: function() {
                                            if (result.length > 0) {
                                                $(this).closest('.ui-dialog-content').dialog('close');
                                                $(this).remove();
                                                state = 0;
                                                errorElement = result[errorIndex].nodes[0].target.toString();
                                                document.querySelector(errorElement).classList.add("toShow");
                                                $(errorElement).hover(function() {
                                                    $(this).removeClass("toShow");
                                                });
                                            }
                                        }}],
                                });
                            }
                        });
                    } // Axe function
                ); // Axe.run
            }); // GetJSON
        }); // Require
    }, // _displayDialogue
}); // YUI create button


}, '@VERSION@', {"requires": ["color-base", "moodle-editor_atto-plugin"]});
