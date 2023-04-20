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
function local_redmine_extend_navigation_menuuser($returnobject, $user, $context, $course) {
    global $CFG, $DB, $USER;

    // Only site (cohort) teachers can see this link.
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
            //'fullusername' => 'יפעת זית',
        ));
        $usermenuitem->pix = "t/preferences";
        $usermenuitem->title = get_string('myissues', 'local_redmine');
        $usermenuitem->titleidentifier = 'myissues,local_redmine';
        return $usermenuitem;
    }

}