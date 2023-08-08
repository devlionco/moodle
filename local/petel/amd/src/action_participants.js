/* eslint-disable no-undef */
/* eslint-disable no-implicit-globals */
/* eslint-disable no-unused-vars */
/* eslint-disable max-len */
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
 * Javascript main event handler.
 *
 * @module     local_petel/init
 * @package    local_petel
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

// import {add as notifyUser} from "../../../../lib/amd/src/toast";

define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',
    'core/modal_events',
    'core/modal_factory',
    'core/templates',
    'core/toast',
    'core/custom_interaction_events'
], function($, Str, Ajax, Notification, ModalEvents, ModalFactory, Templates, toast, CustomEvents) {

    var uniqueid;

    return {
        init: function(currentcourseid, currentuserid, defaults) {
            let self = this;

            Str.get_strings([
                {key: 'buttoncreatecourse', component: 'local_petel'},
                {key: 'buttonaddsystemgroups', component: 'local_petel'}
            ]).done(function(strings) {

                $("#formactionid option").each(function(index) {
                    if (index === 2) {
                        let name = strings[0];
                        var newOption = '<option id="participants-createcourse" value="#createcourse">' + name + '</option>';
                        $(newOption).insertAfter($(this));

                        name = strings[1];
                        newOption = '<option id="participants-addsystemgroups" value="#addsystemgroups">' + name + '</option>';
                        $(newOption).insertAfter($(this));
                    }
                });

                $('#formactionid').on('change', function(e) {
                    switch ($(this).val()) {
                        case '#addsystemgroups':
                            self.openPopupAddSystemGroups(currentcourseid, currentuserid, defaults);
                            break;
                        case '#createcourse':
                            self.openPopupCourseCreate(currentcourseid, currentuserid, defaults);
                            break;
                      }
                });
            });
        },

        openPopupAddSystemGroups: function(currentcourseid, currentuserid, defaults) {
            var self = this;

            // Set new uniqueid.
            uniqueid = Date.now();

            // Get selected users.
            var users = [];
            $('#participants tbody tr input:checked').each(function(index) {
                users.push($(this).attr("name").replace('user', ''));
            });

            let titlePromise = null;
            if (users.length > 1) {
                titlePromise = Str.get_string('titleaddsystemgroups', 'local_petel', users.length);
            } else {
                titlePromise = Str.get_string('titleaddsystemgroups1', 'local_petel');
            }

            Str.get_strings([
                {key: 'selectgroups', component: 'local_petel'}
            ]).done(function(strings) {

                systemgroupsac = {
                    title: strings[0],
                    inputid: 'system_groups_ac' + uniqueid,
                    methodname: 'get_system_groups_ac',
                };

                const context = {

                    autocompletemultiplefields: [
                        systemgroupsac
                    ],
                };

                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: Templates.render('local_petel/popup_action_participants', context),
                    title: titlePromise,
                    buttons: {
                        save: titlePromise,
                    },
                    removeOnClose: true,
                })
                    .then(modal => {
                        modal.getRoot().on(ModalEvents.save, () => self.submitPopupAddSystemGroups(currentcourseid, currentuserid, users));
                        modal.show();
                        return modal;
                    });
            });
        },

        submitPopupAddSystemGroups: function(currentcourseid, currentuserid, users) {

            var Selector = {
                GROUPS_SELECT: '.modal.show #system_groups_ac' + uniqueid,
            };

            // Check data.
            $(Selector.GROUPS_SELECT).parent().find('.error-block').hide();

            let groupids = $(Selector.GROUPS_SELECT).val();

            if (groupids.length !== 0) {
                var data = {
                    groupids: JSON.stringify(groupids),
                    currentuserid: currentuserid,
                    users: JSON.stringify(users)
                };

                Ajax.call([{
                    methodname: 'local_petel_create_system_groups_for_teachers',
                    args: data,
                    done: function(response) {

                        if (response.result) {
                            Str.get_strings([
                                {key: 'coursescreated', component: 'local_petel'}
                            ]).done(function(strings) {
                                toast.add(strings[0]);
                            });
                        }
                    },
                    fail: Notification.exception
                }]);
            } else {
                if (groupids.length === 0) {
                    $(Selector.GROUPS_SELECT).parent().find('.error-block').show();
                }
                return false;
            }
        },

        openPopupCourseCreate: function(currentcourseid, currentuserid, defaults) {
            var self = this;

            // Set new uniqueid.
            uniqueid = Date.now();

            // Get selected users.
            var users = [];
            $('#participants tbody tr input:checked').each(function(index) {
                users.push($(this).attr("name").replace('user', ''));
            });

            let titlePromise = null;
            if (users.length > 1) {
                titlePromise = Str.get_string('titlecreatecourse', 'local_petel', users.length);
            } else {
                titlePromise = Str.get_string('titlecreatecourse1', 'local_petel');
            }

            Str.get_strings([
                {key: 'selectmaincategory', component: 'local_petel'},
                {key: 'selecttemplatecourse', component: 'local_petel'},
                {key: 'selectrole', component: 'local_petel'},
                {key: 'selectgroups', component: 'local_petel'},
                {key: 'keynull', component: 'local_petel'},
            ]).done(function(strings) {

                categoriesac = {
                    title: strings[0],
                    placeholder: strings[0],
                    inputid: 'categories_ac' + uniqueid,
                    methodname: 'get_categories_ac',
                    paramname: 'main-category',
                    paramid: 'main-category' + uniqueid,
                    paramvalue: defaults.categories_ac.value,
                    paramdesc: defaults.categories_ac.name,
                };
                coursesac = {
                    title: strings[1],
                    placeholder: strings[1],
                    inputid: 'courses_ac' + uniqueid,
                    methodname: 'get_courses_ac',
                    paramname: 'template-course',
                    paramid: 'template-course' + uniqueid,
                    paramvalue: defaults.courses_ac.value,
                    paramdesc: defaults.courses_ac.name,
                };
                rolesac = {
                    title: strings[2],
                    placeholder: strings[2],
                    inputid: 'roles_ac' + uniqueid,
                    methodname: 'get_roles_ac',
                    paramname: 'template-role',
                    paramid: 'template-role' + uniqueid,
                    paramvalue: defaults.roles_ac.value,
                    paramdesc: defaults.roles_ac.name,
                };
                groupsac = {
                    title: strings[3],
                    inputid: 'system_groups_ac' + uniqueid,
                    methodname: 'get_system_groups_ac',
                };
                keynull = {
                    title: strings[4],
                    paramname: 'template-keynull',
                    inputid: 'keynull' + uniqueid,
                    paramvalue: defaults.roles_ac.value,
                };

                const context = {
                    autocompletefields: [
                        categoriesac,
                        coursesac,
                        rolesac
                    ],

                    autocompletemultiplefields: [
                        groupsac
                    ],

                    checkbox: [
                        keynull
                    ],
                };

                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: Templates.render('local_petel/popup_action_participants', context),
                    title: titlePromise,
                    buttons: {
                        save: titlePromise,
                    },
                    removeOnClose: true,
                })
                    .then(modal => {
                        modal.getRoot().on(ModalEvents.save, () => self.submitPopupCourseCreate(currentcourseid, currentuserid, users));
                        modal.show();
                        return modal;
                    });
            });
        },

        submitPopupCourseCreate: function(currentcourseid, currentuserid, users) {

            var Selector = {
                CATEGORY_SELECT: '.modal.show #main-category' + uniqueid,
                COURSE_SELECT: '.modal.show #template-course' + uniqueid,
                ROLE_SELECT: '.modal.show #template-role' + uniqueid,
                GROUPS_SELECT: '.modal.show #system_groups_ac' + uniqueid,
                NULL_CHECKBOX: '.modal.show #keynull' + uniqueid,
            };

            // Check data.
            $(Selector.CATEGORY_SELECT).parent().find('.error-block').hide();
            $(Selector.COURSE_SELECT).parent().find('.error-block').hide();
            $(Selector.ROLE_SELECT).parent().find('.error-block').hide();
            $(Selector.GROUPS_SELECT).parent().find('.error-block').hide();
            $(Selector.NULL_CHECKBOX).parent().find('.error-block').hide();

            let categoryid = $(Selector.CATEGORY_SELECT).val();
            let courseid = $(Selector.COURSE_SELECT).val();
            let roleid = $(Selector.ROLE_SELECT).val();
            let groups = $(Selector.GROUPS_SELECT).val();
            let nullcheck = $(Selector.NULL_CHECKBOX).is(':checked');

            if (categoryid.length !== 0 && courseid.length !== 0 && roleid.length !== 0) {
                var data = {
                    categoryid: categoryid,
                    courseid: courseid,
                    roleid: roleid,
                    groups: JSON.stringify(groups),
                    nullcheck: nullcheck,
                    currentuserid: currentuserid,
                    users: JSON.stringify(users)
                };

                Ajax.call([{
                    methodname: 'local_petel_create_courses_for_teachers',
                    args: data,
                    done: function(response) {

                        if (response.result) {
                            Str.get_strings([
                                {key: 'coursescreated', component: 'local_petel'}
                            ]).done(function(strings) {
                                toast.add(strings[0]);
                            });
                        }
                    },
                    fail: Notification.exception
                }]);
            } else {
                if (categoryid.length === 0) {
                    $(Selector.CATEGORY_SELECT).parent().find('.error-block').show();
                }

                if (courseid.length === 0) {
                    $(Selector.COURSE_SELECT).parent().find('.error-block').show();
                }

                if (roleid.length === 0) {
                    $(Selector.ROLE_SELECT).parent().find('.error-block').show();
                }

                // if (groups.length === 0) {
                //     $(Selector.GROUPS_SELECT).parent().find('.error-block').show();
                // }
                //
                // if (!nullcheck) {
                //     $(Selector.NULL_CHECKBOX).parent().find('.error-block').show();
                // }

                return false;
            }
        },

        waitForElementToDisplay(selector, callback, checkFrequencyInMs, timeoutInMs) {
        var startTimeInMs = Date.now();
        (function loopSearch() {
            if (document.querySelector(selector) != null) {
                callback();
                return;
            } else {
                setTimeout(function() {
                    if (timeoutInMs && Date.now() - startTimeInMs > timeoutInMs) {
                        return;
                    }
                    loopSearch();
                }, checkFrequencyInMs);
            }
        })();
    }
    };
});