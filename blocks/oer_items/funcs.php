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
 * Course list block.
 *
 * @package    block_oer_items
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

function block_oer_items_render_content_block() {
    global $OUTPUT, $DB, $USER;

    // Build select.
    $courses = array();
    $defaultcourse = array();

    // Get default course.
    $savedcourses = $DB->get_records('block_oer_items', array('userid' => $USER->id));
    foreach ($savedcourses as $item) {
        $defaultcourse[] = $item->courseid;
    }

    // Add option "All courses".
    $option = [
            'name' => get_string('selectallcourses', 'block_oer_items'),
            'courseid' => 0,
            'active' => in_array(0, $defaultcourse) ? true : false
    ];
    $courses[] = $option;

    $menumaagar = \community_oer\main_oer::structure_main_catalog();
    foreach ($menumaagar as $obj) {
        foreach ($obj['courses'] as $course) {
            $tmp = array();
            $tmp['name'] = $course->fullname;
            $tmp['courseid'] = $course->id;
            $tmp['active'] = in_array($course->id, $defaultcourse) ? true : false;
            $courses[] = $tmp;
        }
    }

    if (empty($defaultcourse)) {
        foreach ($courses as $key => $item) {
            if ($item['courseid'] == 0) {
                $courses[$key]['active'] = true;
            }
        }
    }

    $render = '';

    $data = new \StdClass();
    $data->courses = $courses;
    $data->has_select_menu = !empty($courses) ? true : false;
    $data->content = $render;
    $data->content_empty = !empty($render) ? false : true;
    $data->pix_no_courses = $OUTPUT->image_url('courses', 'block_oer_items');

    $defaultlang = 'עברית';
    $langoptions = [];

    $langoptions[] = [
            'key' => 'all',
            'name' => get_string('selectalllanguages', 'block_oer_items'),
            'active' => 'all' == $defaultlang ? true : false
    ];

    if ($language = \local_metadata\mcontext::module()->getField('language')) {
        $res = preg_split('/\R/', $language->param1);

        foreach (array_unique($res) as $lang) {
            $langoptions[] = [
                    'key' => $lang,
                    'name' => $lang,
                    'active' => $lang == $defaultlang ? true : false
            ];
        }

    }

    $data->langoptions = $langoptions;

    return $OUTPUT->render_from_template('block_oer_items/layout', $data);
}

function block_oer_items_render_activities_content($language, $courseids = []) {
    global $DB;

    $html = '';

    // Check courseids and language.
    if (empty($courseids) || empty($language)) {
        return $html;
    }

    $courseids = array_unique($courseids);

    // All courses.
    if (isset($courseids[0]) && $courseids[0] == 0) {
        $menumaagar = \community_oer\main_oer::structure_main_catalog();
        $courseids = [];
        foreach ($menumaagar as $obj) {
            foreach ($obj['courses'] as $course) {
                $courseids[] = $course->id;
            }
        }
    }

    $data = [];

    $activity = new \community_oer\activity_oer();

    $obj = $activity->query();
    foreach ($courseids as $key => $courseid) {

        if ($key == 0) {
            $obj = $obj->compare('courseid', $courseid);
        } else {
            $obj = $obj->orCompare('courseid', $courseid);
        }

        // Language.
        if ($language != 'all') {
            $obj = $obj->like('metadata_language', $language);
        }

        // Tags.
        $findtag = trim(get_config('block_oer_items', 'tag'));
        if (!empty($findtag)) {
            $obj = $obj->like('tags', $findtag);
        }

    }

    $obj = $obj->compare('visible', '1')->groupBy('cmid')->groupBy('mod_name')->orderString('cm_created', 'desc');

    // Limit 10 items.
    $obj = $obj->limit(1, 10);

    foreach ($courseids as $courseid) {
        $course = $DB->get_record('course', array('id' => $courseid));
        $group = $obj->compare('courseid', $course->id);
        $group = $activity->calculate_data_online($group, 'oercatalog');

        if (!empty($course) && count($group->get()) > 0) {
            $tmp = new \StdClass();
            $tmp->title = '<br><h2>' . $course->fullname . '</h2>';
            $tmp->blocks = array_values($group->get());
            $data[] = $tmp;
        }
    }

    return $data;
}
