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
 * Javascript for collapse button.
 *
 * @package    format_flexsections
 * @copyright  2020 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    [
        'jquery',
        'core/str'
    ],
    function ($, Str ){

        const openHash = () => {
            let queryString = window.location;
            let urlParams = new URLSearchParams(queryString);
            let hash = urlParams.get('hash');

            if(hash.length > 0){
                collapseAction('close');

                let number = hash.match(/\d+/)[0];
                if(number > 0){
                    let id = '#collapssesection' + number;
                    let status = $(id).attr("aria-expanded");
                    $(id).click();
                }
            }
        };

        const collapseAction = (type) => {
            $('.course-section').each(function( index ) {
                let number = $(this).data('number');

                if(number > 0){
                    let id = '#collapssesection' + number;
                    let status = $(id).attr("aria-expanded");

                    if((type === 'open' && status === 'false') || (type === 'close' && status === 'true')){
                        $(id).click();
                    }
                }
            });
        };

        return {
            init: function (callback) {

                // Default state close.
                setTimeout(function() {
                    collapseAction('close');
                    openHash();
                }, 500);

                Str.get_strings([
                    { key: 'collapsebuttonopen', component: 'format_flexsections' },
                    { key: 'collapsebuttonclose', component: 'format_flexsections' }
                ]).done(function (strings) {

                    // Change in url string. Hash.
                    window.addEventListener('hashchange', function(){
                        openHash();
                    });

                    $('#collapse_button').on( "click", function() {
                        let action = $(this).data('action');

                        switch(action) {
                            case 'close':
                                $(this).data('action', 'open').text(strings[1]);
                                collapseAction('open');
                                break;

                            case 'open':
                                $(this).data('action', 'close').text(strings[0]);
                                collapseAction('close');
                                break;

                            default:
                                $(this).data('action', 'close').text(strings[0]);
                        }
                    });
                });
            },
        };
    });
