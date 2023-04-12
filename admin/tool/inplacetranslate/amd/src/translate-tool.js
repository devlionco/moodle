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

/**
 * Javascript for the translate tool.
 *
 * @module     tool/inplacetranslate
 * @package    tool
 * @subpackage inplacetranslate
 * @copyright  2021 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.9
 */

define([
    'jquery',
    'core/str',
    'core/ajax',
    'theme_boost/popover',
], function ($, Str, Ajax) {
    let strings;
    function showPopover(el, txt) {
        let stringidentifier = el.data('string');
        $('.edit-translation').popover('dispose');
        let popoverTemplate = `<div class="popover" role="button">
                <div class="arrow"></div>
                <h3 class="popover-header"></h3>
                <div class="popover-body"></div>
                <div class="popover-footer text-center">
                    <button type="button" class="popover-translation-close btn btn-default mr-1">${strings[1]}</button><button type="button" class="popover-translation-update btn btn-primary mr-1" data-string="${stringidentifier}">${strings[2]}</button>
                </div>
            </div>`;
        let content = `<div class="popover-translation-wrapper">
                <div class="popover-translation-string">${txt}</div>
            </div>
            `;
        $.fn.popover.Constructor.Default.whiteList.button = ['data-string'];
        $.fn.popover.Constructor.Default.whiteList.span = ['contenteditable'];
        $(el).popover({
            container: 'body',
            content: content,
            template: popoverTemplate,
            placement: "top",
            title: strings[3],
            html: true
        });
        el.popover('show');
    }
    function bindEvents() {
        $(document).on('click', '.edit-translation', function(e) {
            e.preventDefault();
            let button = $(this);
            let identifier = button.data('string');
            Ajax.call([{
                methodname: "tool_inplacetranslate_translation_get_string",
                args: {
                    identifier: identifier
                },
                done: function (res) {
                    var result = JSON.parse(res);
                    showPopover(button, result.string);
                },
                fail: Notification.exception
            }]);
        });
        $(document).on('click', '.popover-translation-close', function(e) {
            e.preventDefault();
            $('.edit-translation').popover('dispose');
        });
        $(document).on('click', '.popover-translation-update', function(e) {
            e.preventDefault();
            let editblock = $(this).parents('.popover');
            let content = editblock.find('.popover-translation-string').html();
            let identifier = $(this).data('string');
            Ajax.call([{
                methodname: "tool_inplacetranslate_translation_update_string",
                args: {
                    string: content,
                    identifier: identifier
                },
                done: function (res) {
                    var result = JSON.parse(res);
                    if (result.error == false) {
                        editblock.find('.popover-body').html(result.response);
                        editblock.find('.popover-footer').slideUp();
                    }
                },
                fail: Notification.exception
            }]);
        });
    }
    function initPage() {
        $(document).ready(function () {
            // Scan page for language strings.
            const regex = /{((.*?)\/(.*?))}/gm;
            let pagehtml = $('body').html();
            let newpagehtml = pagehtml;
            let attrelems = {};
            let attrpairs = [];
            let attrexists = [];
            let m;
            while ((m = regex.exec(pagehtml)) !== null) {
                if (m[0].includes('amd.')){continue;}
		// This is necessary to avoid infinite loops with zero-width matches
                if (m.index === regex.lastIndex) {
                    regex.lastIndex++;
                }
                // The result can be accessed through the `m`-variable.
                m.forEach((match, groupIndex) => {
                });
                let t2 = $(':contains(' + m[0] + ')');
                if (t2.length == 0) { // Match tag attribute value
                    let regexcontain = new RegExp("(?![aria\\-label])([a-zA-Z0-9_-]+)=\"(((?!\").)+?){" + m[1] + "}\"", "gm"); // Simple pattern, without string, just def {/}.
                    let cont = pagehtml.match(regexcontain);
                    if (cont && cont.length) {
                        cont.forEach((el) => {
                            if (!attrexists.includes(el)) {
                                attrexists.push(el);
                                attrpairs.push([m[3], el]);
                            }
                        });
                    }
                } else { // Match text.
                    let currentelement = t2[t2.length - 1]; // Use the lowest element.
                    if (!$(currentelement).hasClass('addedtrtool')) {
                        // Add edit button.
                        let regexcurrent = new RegExp("{" + m[1] + "}", "gm");
                        let newelem = m[0] + '<span class="edit-translation textem" data-string="' + m[1] + '">'+strings[0]+'</span>';
                        $(currentelement).html($(currentelement).html().replace(regexcurrent, newelem));
                        $(currentelement).addClass('addedtrtool');
                    }
                }
            }
            attrpairs.forEach(function(elem){
                let identifier = elem[1];
                let q = $("[" + identifier + "]:visible");
                if (q.length) {
                    q.each(function(ind, element) {
                        $(element).after('<span class="edit-translation" data-string="' + elem[0] + '">'+strings[0]+'</span>');
                    });
                }
            });
            bindEvents();
        });
    }
    return {
        init: function () {
            Str.get_strings([
                {
                    key:        'edit',
                    component:  'tool_inplacetranslate'
                },
                {
                    key:        'cancel',
                    component:  'tool_inplacetranslate'
                },
                {
                    key:        'update',
                    component:  'tool_inplacetranslate'
                },
                {
                    key:        'translate_string',
                    component:  'tool_inplacetranslate'
                }
            ])
                .then(function(s) {
                    strings = s;
                    initPage();
                })
                .catch();
        }
    };
});
