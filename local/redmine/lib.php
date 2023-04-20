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
 * Plugin administration pages are defined here.
 *
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Standard callback to add menu item to course-level navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param context $context The context of the course
 */
/*
function local_redmine_extend_navigation_course(navigation_node $navigation, stdClass $course, context $context) {
    global $CFG;

    $CFG->customusermenuitems = $CFG->customusermenuitems.'myissues,local_redmine|/local/redmine/search_issues.php|t/preferences';
}
*/

/**
 * This function extends the course navigation with the report items
 *
 * @param stdClass $returnobject The navigation node to extend
 * @param stdClass $user
 * @param stdClass $context
 * @param stdClass $course The course to object for the report
 */
// Disabled, after we added full support menu functionality to main toolbar.
/*
function local_redmine_extend_navigation_menuuser($returnobject, $user, $context, $course) {
    global $CFG, $DB, $USER;

    // Only site (cohort) teachers can see this link.
    if (!empty($CFG->siteteachers)) {
        $teachers_cohort = $CFG->siteteachers;//'teachers';
        $sql = "SELECT *
            FROM {cohort_members} cm
            JOIN {cohort} c ON c.id = cm.cohortid
            WHERE c.idnumber=? AND cm.userid=?";
        $isteacher = $DB->get_records_sql($sql, [$teachers_cohort, $USER->id]);

        if ($isteacher) {
            $usermenuitem = new stdClass();
            $usermenuitem->itemtype = 'link';
            $usermenuitem->url = new moodle_url('/local/redmine/search_issues.php', array(
                //'fullusername' => 'שם של מורה',
            ));
            $usermenuitem->pix = "t/preferences";
            $usermenuitem->title = get_string('myissues', 'local_redmine');
            $usermenuitem->titleidentifier = 'myissues,local_redmine';
            return $usermenuitem;
        }
    }
}
*/

function local_redmine_render_navbar_output() {
    global $PAGE, $CFG;

    if(isloggedin()) {
        $PAGE->requires->js_call_amd('local_redmine/support', 'init', []);
    }

    return '';
}

/**
 * Serves the files from the hvp file areas
 *
 * @package mod_hvp
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the newmodule's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 *
 * @return true|false Success
 */
function local_redmine_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {

    $filename = array_pop($args);
    $itemid = array_shift($args);
    $filepath = '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_redmine', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // No such file.
    }

    if ($file->is_valid_image()) {
        $forcedownload = true;
    }

    // Totara: use allowxss option to prevent application/x-javascript mimetype
    // from being converted to application/x-forcedownload.
    $options['allowxss'] = '1';

    send_stored_file($file, 86400, 0, $forcedownload, $options);

    return true;
}