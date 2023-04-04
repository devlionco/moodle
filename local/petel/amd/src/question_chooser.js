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
 * A javascript module to handle toggling activity chooser recommendations.
 *
 * @module     local_petel/question_chooser
 * @copyright  2020 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(
    [
        'jquery',
        'core/ajax',
        'core/notification'
    ],
    function($, Ajax, Notification) {

        function toggleTabAll(checked, qtype){

            let obj = $('#tab_all').find('*[data-qtype="'+qtype+'"]');

            switch (checked) {
                case 1:
                    $(obj).data('checked', 0);
                    $(obj).removeClass('fa-star').addClass('fa-star-o');
                    break;
                case 0:
                    $(obj).data('checked', 1);
                    $(obj).removeClass('fa-star-o').addClass('fa-star');
                    break;
            }
        }

        function toggleTabFavorites(checked, qtype){

            let obj = $('#tab_favorites').find('*[data-qtype="'+qtype+'"]');

            $(obj).removeClass('fa-star-o').addClass('fa-star');

            switch (checked) {
                case 1:
                    $(obj).data('checked', 0);
                    $(obj).parent().parent().removeClass('d-flex').addClass('d-none');
                    break;

                case 0:
                    $(obj).data('checked', 1);
                    $(obj).parent().parent().removeClass('d-none').addClass('d-flex');
                    break;
            }
        }

        return {
            init: function(callback) {
                $(document).on( "click", '.qfavorites', function() {

                    let checked = $(this).data('checked');
                    let qtype = $(this).data('qtype');

                    toggleTabAll(checked, qtype);
                    toggleTabFavorites(checked, qtype);

                    let selected = [];
                    $('#tab_favorites').find('i').each(function( index ) {
                        if($(this).data('checked') === 1) {
                            selected.push($(this).data('qtype'));
                        }
                    });

                    Ajax.call([{
                        methodname: 'local_petel_save_qtypes_favorites',
                        args: {
                            qtypes: JSON.stringify(selected)
                        },
                        done: function (response) {

                            if (response.result) {
                                callback(true);
                            }else{
                                callback(false);
                            }
                        },
                        fail: Notification.exception
                    }]);

                })
            }
        };
    });
