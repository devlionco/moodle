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
 * Some UI stuff for participants page.
 * This is also used by the report/participants/index.php because it has the same functionality.
 *
 * @module     quiz_correctanswer/main
 * @package    quiz_correctanswer
 * @copyright  2017 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Devlion Moodle Development <service@devlion.co>
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/templates', 'core/notification', 'core/ajax'],
    function ($, Str, ModalFactory, ModalEvents, Templates, Notification, Ajax) {

        const loadingIcon = () => {
            var shadowStyle="display: flex; position: fixed; z-index: 99999; top: 0; bottom: 0; right: 0; left: 0; background: #0000006b;"
            var spinnerStyle = "margin: auto;" +
                "width: 6rem;" +
                "height: 6rem;" +
                "border: .6em solid #fff;" +
                "border-right-color: transparent;" +
                "border-radius: 50%;" +
                "animation: spinner-border .75s linear infinite;";
            var spinner = '<div class = "spinner" style = "'+ spinnerStyle +'"></div>';
            var shadow = $('<div class = "loading" style="'+ shadowStyle +'">'+ spinner +'</div>');
            $('body').append(shadow);
        }

        const removeLoadingIcon = () => {
            $('.loading').remove();
        }

        return {

            'init': function () {

                // Print
                $("#ca_print").click(function() {
                    window.print();
                });

                return true;
            }
        };
    });
