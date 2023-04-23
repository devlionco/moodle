<?php
require_once('../../config.php');
require_login();

if(!defined('AJAX_SCRIPT')){
    define('AJAX_SCRIPT', true);
}
require_once($CFG->libdir . '/datalib.php');
global $CFG, $USER;
$query = required_param('query', PARAM_RAW);
$total            = 0;
$courses['query'] = $query;
$course_count = optional_param('course_count' , 10 , PARAM_INT); // Default value for course result list
$mycoursesflag = optional_param('my_courses_flag', 'false', PARAM_ALPHA);

// PTL-4404 remove oer catalog courses (if not admin).
$except_courses_sql = [' c.category NOT IN (:p1) '];
list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();
if (!is_admin($USER->id) && !empty($categories)) {
    $exceptcatssqlparams = ['p1' => implode(',', $categories)];
    $exceptcatsarray = $categories;
} else {
    // Admin sees all courses.
    $exceptcatssqlparams = ['p1' => '0'];
    $exceptcatsarray = [0];
}

if ($mycoursesflag === "true") {
    // TODO: fix me, enrol_get_my_courses was not returning user's course list. weird.
    //$courses['results'] = enrol_get_my_courses(array('id', 'shortname'), 'visible DESC,sortorder ASC', $course_count);
    $courses['results'] = array_values(
        get_courses_search(array($query), 'fullname ASC', 0, $course_count, $total, [],
            $except_courses_sql, $exceptcatssqlparams));

    //Once you have the results, filter the ones matching the search query
    $mycourses          = array();
    foreach ($courses['results'] as $objCourse) {
        // Remove OER catalog courses from search.
        if (in_array($objCourse->category, array_values($exceptcatsarray))) continue;

        // Can only see courses user have access to.
        $course_context= context_course::instance($objCourse->id);
        if (is_enrolled($course_context)) {
            $mycourses[] = $objCourse;
        }
    }
    $courses['results'] = array_values($mycourses);
    echo json_encode($courses);
    
} else {
    $courses['results'] = array_values(
        get_courses_search(array($query), 'fullname ASC', 0, $course_count, $total, [],
            $except_courses_sql, $exceptcatssqlparams));
    if (empty($courses['results'])) {
        $objCourse          = new stdClass();
        $objCourse->id      = 'na';
        $objCourse->msg      = get_string('noresults','block_searchcourses');
        $courses['results'] = array_values(array(
            $objCourse
        ));
        echo json_encode($courses);
    } else {
        echo json_encode($courses);
    }
}

function is_admin($userid) {

    $admins = get_admins();
    foreach ($admins as $admin) {
        if ($userid == $admin->id) {
            return true;
        }
    }
    return false;
}