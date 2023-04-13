<?php
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
 * Local plugin "OER catalog" - Library
 *
 * @package    local_petel
 * @copyright  2017 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

function local_petel_before_footer() {
    global $PAGE, $USER, $CFG;

    if ($PAGE->pagetype !== 'mod-hvp-view') {
        return;
    }

    $sesskey = sesskey();
    $logurl = $CFG->wwwroot . '/local/petel/timeonpage.php';

    // Count the time a user was looking at the page (page was in focus).
    $PAGE->requires->js_amd_inline("
        require(['jquery'], function($) {

            $(document).ready(function() {
                var start = new Date();
                // Save user's focus time on page.
                // end - start = all the time the page was visible, but not necessarily focused.
                /*
                // This method pause user navigation, it is sync.
                $(window).on('beforeunload', function(e) {
                    console.log('Total focus time on page = ' + window.timercounter + ' sec.');
                    var end = new Date();
                    $.ajax({
                        type: 'POST',
                        url: '{$logurl}',
                        data: {
                            'timespent': window.timercounter,
                            'contextid': {$PAGE->context->id},
                            'userid': {$USER->id},
                            'sesskey': '{$sesskey}'},
                        //async: false
                    })
                });
                */

                // https://usefulangle.com/post/62/javascript-send-data-to-server-on-page-exit-reload-redirect
                // This method does not pause user navigation, it is async.
                $(window).on('beforeunload', function(e) {
                    var fd = new FormData();
	                fd.append('timespent', window.timercounter);
	                fd.append('contextid', {$PAGE->context->id});
	                fd.append('userid', {$USER->id});
	                fd.append('sesskey', '{$sesskey}');

	                navigator.sendBeacon('$logurl', fd);
                });

                // Let's start counting user focus on page, immediately after it fully loads
                startTimer();
            });
        
            window.timercounter = 0;
            window.timerstate = 0;
            var myInterval;
            // Active
            window.addEventListener('focus', startTimer);
            
            /*
            window.addEventListener('focus', function(){
              if (document.activeElement instanceof HTMLIFrameElement) {
                console.log('Wow! Iframe Click!');
                if (window.timerstate == 0) {
                    console.log('IFRAME: start timer');
                    startTimer();
                } else {
                    console.log('IFRAME: stop timer');
                    stopTimer();
                }
              } else {
                  startTimer();
              }
            });
            */

            // Inactive
            window.addEventListener('blur', stopTimer);
            
            function timerHandler() {
                window.timercounter++;
                //document.getElementById('seconds').innerHTML = timercounter;
                //console.log('timer ON ' + window.timercounter + ' sec.');
            }
            
            // Start timer
            function startTimer() {
                window.timerstate = 1;
                console.log('got focus (timer ON) @ ' + window.timercounter + ' sec.');
                window.clearInterval(myInterval);
                myInterval = window.setInterval(timerHandler, 1000);
            }
            
            // Stop timer
            function stopTimer() {
                if (document.activeElement instanceof HTMLIFrameElement) {
                    console.log('Wow! Iframe Click!');
                } else {
                    window.timerstate = 0;
                    console.log('lost focus (time OFF) @ ' + window.timercounter + ' sec.');
                    window.clearInterval(myInterval);
                }
            }
        });
    ");
}

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function local_petel_render_navbar_output() {
    global $PAGE, $USER, $DB;

    if (has_capability('moodle/site:config', context_system::instance())) {
        if (strpos($PAGE->url->get_path(), 'user/index.php') !== false) {

            $defaultrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
            $defaultrole->name = !empty($defaultrole->name) ? $defaultrole->name : get_string('defaultcourseteacher');

            $defaults = [
                    'categories_ac' => ['name' => '', 'value' => ''],
                    'courses_ac' => ['name' => '', 'value' => ''],
                    'roles_ac' => [
                            'name' => $defaultrole->name,
                            'value' => $defaultrole->id
                    ],
            ];

            $data = array(
                    $PAGE->course->id,
                    $USER->id,
                    $defaults
            );
            $PAGE->requires->js_call_amd('local_petel/action_participants', 'init', $data);
        }
    }

    if (!empty(get_config('local_petel', 'enabledemo'))) {
        $PAGE->requires->js_call_amd('local_petel/demo', 'init');
    }

    if (!empty(get_config('local_petel', 'default_course')) && !empty(get_config('local_petel', 'admin_email'))) {
        $data = array(
                $USER->id
        );
        $PAGE->requires->js_call_amd('local_petel/createcourse', 'init', $data);
    }

    if ($PAGE->pagetype == 'course-edit') {
        $PAGE->requires->js_call_amd('local_petel/editcourse', 'init', []);
    }

    // PTL-6739.
    if ($PAGE->pagetype == 'mod-assign-grading') {
        $PAGE->requires->js_amd_inline("require(['jquery', 'local_petel/assign_participants'], function($, Participants) {
            M.assign_participants = Participants;        
        })");
    }

    // PTL-2405.
    if (in_array($PAGE->pagetype, ['mod-checklist-report', 'mod-checklist-edit', 'mod-checklist-view'])) {
        $PAGE->requires->js_call_amd('local_petel/moodle_plugins', 'mod_checklist_add_tab_settings', [$PAGE->cm->id]);
    }

    // PTL-4614.
    if (in_array($PAGE->pagetype, ['mod-questionnaire-preview', 'mod-questionnaire-questions',
            'mod-questionnaire-show_nonrespondents', 'mod-questionnaire-qsettings'])) {
        $PAGE->requires->js_call_amd('local_petel/moodle_plugins', 'mod_questionnaire_add_tab_settings', [$PAGE->cm->id]);
    }

    // PTL-4614.
    if (in_array($PAGE->pagetype, ['mod-questionnaire-view'])) {
        $PAGE->requires->js_call_amd('local_petel/moodle_plugins', 'mod_checklist_view_add_tab_settings', [$PAGE->cm->id]);
    }

    // PTL-4690.
    $PAGE->requires->js_call_amd('local_petel/events', 'init', []);
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function local_petel_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE;

    $url = new moodle_url('/local/petel/sessiontimeout.php', ['userid' => $user->id]);

    $contactcategory = new core_user\output\myprofile\category('catcustomsettings',
            get_string('catcustomsettings', 'local_petel'));
    $tree->add_category($contactcategory);

    $node = new core_user\output\myprofile\node('catcustomsettings', 'sessiontimeout',
            get_string('sessiontimeout', 'local_petel'), null, $url);
    $tree->add_node($node);
}

/**
 * This function extends the course navigation with the report items
 *
 * @param stdClass $returnobject The navigation node to extend
 * @param stdClass $user
 * @param stdClass $context
 * @param stdClass $course The course to object for the report
 */
/*
 * TODO: refactor darkmode to use this (PTL-2317)
 *
function local_petel_extend_navigation_menuuser($returnobject, $user, $context, $course) {

    $usermenuitem = new stdClass();

    $usermenuitem->itemtype = 'link';
    $usermenuitem->url = new moodle_url('/course/switchrole.php', array(
        'id' => $course->id,
        'switchrole' => -1,
        'returnurl' => ''
    ));
    $usermenuitem->pix = "i/switchrole";
    $usermenuitem->title = get_string('switchroleto');
    $usermenuitem->titleidentifier = 'switchroleto,moodle';

    return $usermenuitem;
}
*/

function local_petel_extend_navigation_course($parentnode, $course, $context) {
    global $OUTPUT, $PAGE, $COURSE, $USER, $DB;

    if (!empty(get_config('local_petel', 'enabledemo'))) {

        $enrol = $DB->get_record_select('enrol', 'courseid = ? AND enrol = ? AND password <> ""', [$COURSE->id, 'self']);

        if (!$enrol) {
            return;
        }

        $flagcourse = false;
        $roles = get_user_roles(\context_course::instance($COURSE->id), $USER->id, false);
        foreach ($roles as $role) {
            if ($role->shortname == 'editingteacher') {
                $flagcourse = true;
            }
        }

        // Check if admin.
        $isadmin = is_siteadmin();

        if ($flagcourse || $isadmin) {
            $title = get_string('linktodemo', 'local_petel');

            $url = 'Javascript:void(0)';
            $coursedemonode = \navigation_node::create($title, $url, \navigation_node::TYPE_CUSTOM,
                    'coursedemo', 'coursedemo',
                    new \pix_icon('e/insert_edit_link', $title, 'theme')
            );

            $class = 'key-' . $enrol->password;
            $coursedemonode->title($class);
            $coursedemonode->add_class($class);
            $class = 'lang-' . current_language();
            $coursedemonode->add_class($class);
            $coursedemonode->add_class('demo-popup-course');
            $parentnode->add_node($coursedemonode);
        }

        $linkitem = '<a href="#" class="dropdown-item demo_popup menu-action cm-edit-action" data-cmid="123XYZ321" data-key="' .
                $enrol->password . '" data-lang="' . current_language() . '" data-action="demo_popup" role="menuitem"
                 title="' . htmlspecialchars(get_string("linktodemoactivity", "local_petel")) . '">'
                . $OUTPUT->pix_icon('fp/link', get_string("linktodemoactivity", "local_petel"), 'theme')
                . '<span class="menu-action-text">' . htmlspecialchars(get_string("linktodemoactivity", "local_petel")) . '</span>'
                . '</a>';

        $enc = json_encode($linkitem);
        $PAGE->requires->js_init_code(<<<EOJS
    var activities = document.querySelectorAll('.section-cm-edit-actions div[role="menu"]');
    if (activities) {
        for (var i = 0; i < activities.length; i++) {
            var ul = activities[i];
            var owner = ul.parentNode.parentNode.parentNode.getAttribute('data-owner');
            if (owner) {
                var id = owner.replace(/^#module-/, '');
                ul.insertAdjacentHTML('beforeend', $enc.replace('123XYZ321', id));
            }
        }
    }
EOJS
                , true);

        $enc = json_encode('<li role="presentation">' . $linkitem . '</li>');
        $PAGE->requires->js_init_code(<<<EOJS
    var activities = document.querySelectorAll('.section-cm-edit-actions ul[role="menu"]');
    if (activities) {
        for (var i = 0; i < activities.length; i++) {
            var ul = activities[i];
            var owner = ul.parentNode.getAttribute('data-owner');
            if (owner) {
                var id = owner.replace(/^#module-/, '');
                ul.insertAdjacentHTML('beforeend', $enc.replace('123XYZ321', id));
            }
        }
    }
EOJS
                , true);
    }
}

function local_petel_after_config() {
    global $CFG, $USER;
    if (isset($USER->id)) {
        $CFG->sessiontimeout = local_petel_get_session_timeout($USER->id);
    }
}
