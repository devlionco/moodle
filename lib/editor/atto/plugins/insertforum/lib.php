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
 * Atto text editor integration version file.
 *
 * @package    atto_insertforum
 * @author     Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//use \mod_insertforum\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise this plugin
 * @param string $elementid
 */
function atto_get_capability() {
    global $COURSE;

    $context = context_course::instance($COURSE->id);

    return has_capability('moodle/course:update', $context);
}

function atto_insertforum_strings_for_js() {
    global $PAGE;


    $PAGE->requires->strings_for_js(array('forum',
        'insertforum',
        'noforums',
        'groups',
        'nogroups',
        'groupings',
        'nogroupings',
        'numposts',
        'select_desc',
        'title'),
        'atto_insertforum');
}

function atto_insertforum_params_for_js($elementid, $options, $fpoptions) {
    global $COURSE;
    $capability = atto_get_capability();

    if ($options['context']->contextlevel < CONTEXT_COURSE) {
        return array();
    }
    $list_forums = array();
    $insertforums = get_my_forums_by_contextid();
    foreach ($insertforums as $forum) {
        $list_forums[] = array('id' => $forum->id, 'text' => $forum->name);
    }

    $list_groups = array();
    $list_groups[] = array('id' => 0, 'name' => get_string('nogroups', 'atto_insertforum'));
    $course_groups = groups_get_all_groups($COURSE->id);
    foreach ($course_groups as $group) {
        $list_groups[] = array('id' => $group->id, 'name' => $group->name);
    }

    $list_groupings = array();
    $list_groupings[] = array('id' => 0, 'name' => get_string('nogroupings', 'atto_insertforum'));
    $course_groupings = groups_get_all_groupings($COURSE->id);
    foreach ($course_groupings as $grouping) {
        $list_groupings[] = array('id' => $grouping->id, 'name' => $grouping->name);
    }

    return array('forums' => $list_forums, 'groups' => $list_groups, 'groupings' => $list_groupings, 'capability' => $capability);
}

// Load Forums list in course (context)
function get_my_forums_by_contextid() {
    $forums = [];
    global $DB, $PAGE;

    $rawforums = $DB->get_records_sql("SELECT cm.id AS id,
                                     h.name
                                FROM {course_modules} cm,
                                     {course_sections} cw,
                                     {modules} md,
                                     {forum} h                                    
                               WHERE cm.course = ?
                                 AND cm.instance = h.id
                                 AND cm.section = cw.id
                                 AND md.name = 'forum'
                                 AND md.id = cm.module                                 
                             ", array($PAGE->course->id));

    $modinfo = get_fast_modinfo($PAGE->course, NULL);
    if (empty($modinfo->instances['forum'])) {
        $forums = $rawforums;
    } else {
        // Lets try to order these bad boys
        foreach ($modinfo->instances['forum'] as $cm) {
            if (!$cm->uservisible || !isset($rawforums[$cm->id])) {
                continue; // Not visible or not found
            }
            if (!empty($cm->extra)) {
                $rawforums[$cm->id]->extra = $cm->extra;
            }
            $forums[] = $rawforums[$cm->id];
        }
    }
    return $forums;
}
