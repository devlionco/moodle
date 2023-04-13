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
 * Site recommendations for the activity chooser.
 *
 * @package    local_petel
 * @copyright  2020 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$fromcourseid = optional_param('fromcourseid', 0, PARAM_INT);
$tocourseid = optional_param('tocourseid', 0, PARAM_INT);

// Some security.
require_login();

if ($fromcourseid !== 0 && $tocourseid !== 0) {
    for ($courseid = $fromcourseid; $courseid <= $tocourseid; $courseid++) {
        echo "Creating special categories in courseid=" . $courseid;
        create_course_special_grade_categories($courseid);
    }
} else if ($fromcourseid !== 0) {
    echo "Creating special categories in courseid=" . $courseid;
    create_course_special_grade_categories($fromcourseid);
}

echo " Finished ";

function create_course_special_grade_categories($courseid) {
    global $CFG;

    require_once($CFG->libdir . '/grade/constants.php');

    // Add a new "Zero grade" category, for activities without grading.
    echo "<br> [100%] grade cat - Done, ";

    $catnograde = [
            'courseid' => $courseid,
            'fullname' => get_string('activitieswithoutgrade', 'local_petel'),
            'aggregation' => GRADE_AGGREGATE_MEAN
    ];
    $returncat = create_grade_category($catnograde);

    // Set the grade type of the grade item associated to the grade category.
    $catitemnototalinnototal = $returncat->load_grade_item();
    // $catitemnototalinnototal->gradetype = GRADE_TYPE_NONE;
    $catitemnototalinnototal->grademax = 0;
    $catitemnototalinnototal->update();

    echo " [0%] grade cat - Done<br>";

}

function create_grade_category($record = null) {
    global $CFG;

    $record = (array) $record;

    if (empty($record['courseid'])) {
        throw new coding_exception('courseid must be present in testing::create_grade_category() $record');
    }

    if (!isset($record['fullname'])) {
        $record['fullname'] = 'Grade category ';
    }

    // For gradelib classes.
    require_once($CFG->libdir . '/gradelib.php');

    // Create new grading category in this course.
    $gradecategory = new grade_category(array('courseid' => $record['courseid']), false);
    $gradecategory->apply_default_settings();
    grade_category::set_properties($gradecategory, $record);
    $gradecategory->apply_forced_settings();
    $gradecategory->insert();

    // This creates a default grade item for the category.
    $gradeitem = $gradecategory->load_grade_item();

    $gradecategory->update_from_db();
    // return $gradecategory->get_record_data();
    return $gradecategory;
}
