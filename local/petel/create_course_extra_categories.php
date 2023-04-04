<?php

require(__DIR__ . '/../../config.php');

$fromcourseid = optional_param('fromcourseid', 0, PARAM_INT);
$tocourseid = optional_param('tocourseid', 0, PARAM_INT);
//$gradecategorycounter = 0;

// Some security.
require_login();

if ($fromcourseid !== 0 && $tocourseid !== 0) {
    for ($courseid = $fromcourseid; $courseid <= $tocourseid; $courseid++) {
        echo "Creating special categories in courseid=".$courseid;
        create_course_special_grade_categories($courseid);
    }
} elseif ($fromcourseid !== 0) {
    echo "Creating special categories in courseid=".$courseid;
    create_course_special_grade_categories($fromcourseid);
}

echo " Finished ";

function create_course_special_grade_categories($courseid) {
    global $CFG;

    include $CFG->libdir.'/grade/constants.php';

    // Do not create '100 grade' category, and use current/default course category.
    /*
    $cat_fullgrade = [
            'courseid' => $courseid,
            'fullname' => get_string('activitieswithgrade', 'local_petel'),
            'aggregation' => GRADE_AGGREGATE_MEAN
    ];
    $returncat = create_grade_category($cat_fullgrade);
    */

    // Add a new "Zero grade" category, for activities without grading.

    echo "<br> [100%] grade cat - Done, ";

    $cat_nograde = [
        'courseid' => $courseid,
        'fullname' => get_string('activitieswithoutgrade', 'local_petel'),
        'aggregation' => GRADE_AGGREGATE_MEAN
    ];
    $returncat = create_grade_category($cat_nograde);
// Set the grade type of the grade item associated to the grade category.
    $catitemnototalinnototal = $returncat->load_grade_item();
//$catitemnototalinnototal->gradetype = GRADE_TYPE_NONE;
    $catitemnototalinnototal->grademax = 0;
    $catitemnototalinnototal->update();

    echo " [0%] grade cat - Done<br>";

}


function create_grade_category($record = null) {
    global $CFG;

    //$gradecategorycounter++;

    $record = (array)$record;

    if (empty($record['courseid'])) {
        throw new coding_exception('courseid must be present in testing::create_grade_category() $record');
    }

    if (!isset($record['fullname'])) {
        $record['fullname'] = 'Grade category ';// . $gradecategorycounter;
    }

    // For gradelib classes.
    require_once($CFG->libdir . '/gradelib.php');
    // Create new grading category in this course.
    $gradecategory = new grade_category(array('courseid' => $record['courseid']), false);
    $gradecategory->apply_default_settings();
    grade_category::set_properties($gradecategory, $record);
    $gradecategory->apply_forced_settings();
    $gradecategory->insert();

    // This creates a default grade item for the category
    $gradeitem = $gradecategory->load_grade_item();

    $gradecategory->update_from_db();
    //return $gradecategory->get_record_data();
    return $gradecategory;
}