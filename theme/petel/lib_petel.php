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
 * Custom PETEL functions.
 *
 * @package    theme_petel
 * @copyright  2021 Science teaching department, Weizmann institute of science.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$applysitewidecolor = optional_param('applysitewidecolor', null, PARAM_RAW);
if ($applysitewidecolor) {
    petel_clear_cache();
}

function add_user_profile_uigender_to_body() {
    global $DB, $USER, $PAGE;

    $pagelayout = $PAGE->pagelayout;
    // Only activate this feature in main course view or module view pages.
    if ($pagelayout === 'incourse' || $pagelayout === 'course' ) {
        $userinfofield = $DB->get_record('user_info_field', ['shortname' => 'ui_gender']); // Field ui_gender must exists
        if ($userinfofield) {
            $userinfodata = $DB->get_record('user_info_data', ['fieldid' => $userinfofield->id, 'userid' => $USER->id]);
            if ($userinfodata) {
                return strtoupper($userinfodata->data);
            }
        }
    }
    return '';
}

/**
 * Add role class to body
 * @return string
 */
function add_role_class_to_body() {
    global $PAGE, $USER;

    $userrole = 'role-teacher';
    $isstudent = false;
    $isteachercolleague = false;
    $ismanager = false;
    $userroles = get_user_roles($PAGE->context, $USER->id, true);
    foreach ($userroles as $role) {
        if ($role->shortname === 'student') {
            $isstudent = true;
        }
        if ($role->shortname === 'teachercolleague') {
            $isteachercolleague = true;
        }
        if ($role->shortname === 'manager') {
            $ismanager = true;
        }
    }
    if ($isstudent) {
        $userrole = 'role-student';
    }
    if ($isteachercolleague) {
        $userrole = 'role-teachercolleague';
    }
    if ($ismanager) {
        $userrole = 'role-manager';
    }
    if (has_capability('moodle/site:config', context_system::instance())) {
        $userrole = 'role-admin';
    }
    return $userrole;
}

/**
 * Add dark_mod class to body
 * @return string
 */
function add_dark_class_to_body(){

    global $USER;
    $user_dark_mod = get_user_preferences('dark_mode', '', $USER->id);
    if($user_dark_mod){
        return 'dark_mode';
    }

}



/**
 * Add dark_mod class to body
 * @return boolean
 */
function block_expanded_in_course(){
    global $USER, $PAGE;

    $result = false;

    if (get_config('theme_petel', 'blockexpanded')) {
        if (get_user_preferences('blockexpanded', '', $USER->id)) {
            $result = false;
        } else {
            $result = true;
        }
    }

    if(in_array($PAGE->pagetype, ['mod-quiz-attempt', 'mod-quiz-review'])){
        if (get_user_preferences('quizblockexpanded', '', $USER->id)) {
            $result = false;
        } else {
            $result = true;
        }
    }

    return $result;
}

/**
 * Return section progress
 * @param stdClass $course
 * @param stdClass $section
 * @return int
 */
function sectionprogress($course, $section) {
    global $DB, $USER;

    // Get all current user's completions on current course.
    $usercourseallcmcraw = $DB->get_records_sql("
        SELECT
            cmc.*
        FROM
            {course_modules} cm
            INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id
        WHERE
            cm.course=? AND cmc.userid=?", array($course->id, $USER->id));
    $usercmscompletions = array();
    foreach ($usercourseallcmcraw as $record) {
        if ($record->completionstate <> 0) {
            $usercmscompletions[] = $record->coursemoduleid;
        }
    }

    // Get current course's completable cms.
    $ccompetablecms = array();
    $coursefminfo = get_fast_modinfo($course);
    foreach ($coursefminfo->get_cms() as $cm) {
        if ($cm->completion !== COMPLETION_TRACKING_NONE && !$cm->deletioninprogress) {
            $ccompetablecms[] = $cm->id;
        }
    }

    $completedactivitiescount = 0;
    @$scms = $coursefminfo->sections[$section->section];     // Get current section activities.
    if (!empty($scms)) {
        //$allcmsinsectioncount = count($scms);           // First count all cms in section.
        foreach ($scms as $arid => $scmid) {              // For each acivity in section.
            if (!in_array($scmid, $ccompetablecms)) {
                unset($scms[$arid]);                    // Unset cms that are not  completable.
            } else {
                if (in_array($scmid, $usercmscompletions)) {
                    $completedactivitiescount++;        // If cm is compledted - count it.
                }
            }
        }
        $completablecmsinsectioncount = count($scms);   // Count completable activities in section.
        if (!empty($completablecmsinsectioncount)) {    // If section has at least 1 completable activity.
            $csectionprogress = round($completedactivitiescount / $completablecmsinsectioncount * 100);
        } else {
            $csectionprogress = 0;
        }
        return $csectionprogress;
    } else {
        return 0;
    }
}

/**
 * Return section progress html
 * @param obj $course
 * @param obj $section
 * @return string
 */
function getsectionprogress($course, $section) {
    $progress = sectionprogress($course, $section);
    $o = html_writer::start_tag('div', array('class' => 'progressbar_wrap', 'title' => $progress.'%'));
    $o .= html_writer::tag('div', '', array('class' => 'progressbar_line', 'style' => 'width: '.$progress.'%'));
    $o .= html_writer::end_tag('div');

    return $o;
}

/**
 * Return if course from oer_catalog
 * @return bool
 */
function theme_petel_if_course_oer_catalog() {
    global $CFG, $PAGE;

    if ($PAGE->course->id > 1) {
        list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();
        if(in_array($PAGE->course->id, $oercourses)){
            return true;
        }
    }

    return false;
}

/**
 * Return if backtocourse enable
 * @return bool
 */
function getbacktocourse() {
    global $PAGE;

    if ($PAGE->pagelayout === 'incourse') {
        if (!theme_petel_if_course_oer_catalog()) {
            return true;
        }
    }

    return false;
}

/**
 * Render block submission activity only for admin
 * @param cm_info $mod
 * @return array
 */
function petel_cm_grade_status(cm_info $mod) {
    global $CFG, $USER, $DB;
    $data = false;

    // For a teacher colleagues don`t show activity status.
    $modcontext = context_module::instance($mod->id);
    $roles         = get_user_roles($modcontext, $USER->id, false);
    foreach ($roles as $role) {
        if ($role->shortname === 'teachercolleague') {
            return $data;
        }
    }

    if (is_siteadmin() || has_capability('moodle/course:update', $modcontext)) {
        if(in_array($mod->modname, ['assign', 'quiz', 'questionnaire', 'hvp'])) {

            $tooltip = '';
            $segmentgray = $segmentblue = $segmentorange = $segmentgreen = $segmentred = 0;
            $countmaxusers = count(petel_get_students_course($mod->course));

            switch ($mod->modname) {
                case 'questionnaire':
                    require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');

                    $course = get_course($mod->course);

                    if ($questionnaire = $DB->get_record("questionnaire", array("id" => $mod->instance))) {
                        $questionnaire = new \questionnaire(0, $questionnaire, $course, $mod);

                        $incompleteusers = questionnaire_get_incomplete_users($questionnaire->cm, $questionnaire->sid);
                        $countincompleteusers = is_array($incompleteusers) ? count($incompleteusers) : 0;

                        // Started users.
                        $data = $DB->get_records_sql("
                            SELECT * 
                            FROM {questionnaire_response}
                            WHERE questionnaireid = ? AND complete = ?
                            GROUP BY userid
                        ",[$questionnaire->id, 'n']);

                        $countstartedusers = count($data);

                        $data = $DB->get_records_sql("
                            SELECT * 
                            FROM {questionnaire_response}
                            WHERE questionnaireid = ? AND complete = ?
                            GROUP BY userid
                        ",[$questionnaire->id, 'y']);

                        $countcompleteusers = count($data);
                        
                        // Gray - טרם נענה.
                        // Blue - בתהליך.
                        // Green - נענה.

                        $segmentgray = $countincompleteusers;
                        //$segmentblue = $countstartedusers;
                        $segmentgreen = $countcompleteusers;
                    }

                    $url = new moodle_url('/mod/questionnaire/report.php', array('instance' => $mod->instance));

                    // Tooltip.
                    // Status Y תלמידים טרם התחילו.
                    if($countincompleteusers){
                        $tooltip .='<div>' .  $countincompleteusers.' '.get_string('questionnairenotsubmitted', 'theme_petel').' </div>';
                    }

                    // Status X תלמידים הגישו.
                    if($countcompleteusers){
                        $tooltip .='<div>' .  $countcompleteusers.' '.get_string('questionnairesubmitted', 'theme_petel').' </div>';
                    }
                    break;

                case 'assign':
                    require_once($CFG->dirroot . '/mod/assign/locallib.php');

                    list ($course, $cm) = get_course_and_cm_from_cmid($mod->id, 'assign');
                    $context = \context_module::instance($cm->id);
                    $assign = new \assign($context, $cm, $course);
                    $summary = $assign->get_assign_grading_summary_renderable();

                    // Gray - טרם התחיל.
                    // Orange - טרם נבדק.
                    // Green - נבדק, ניתן ציון.

                    // If Groups.
                    if($summary->teamsubmission){
                        $countmaxusers = $summary->participantcount;

                        if(method_exists($assign,'count_teams_submissions_need_grading')){
                            $nothavegrade = $assign->count_teams_submissions_need_grading();
                        }else{
                            $nothavegrade = 0;
                        }

                        $notsubmitted = $countmaxusers - $summary->submissionssubmittedcount;
                        $havegrade = $summary->submissionssubmittedcount - $nothavegrade;

                        $segmentgray = $notsubmitted;
                        $segmentorange = $nothavegrade;
                        $segmentgreen = $havegrade;
                    }else{
                        $countmaxusers = $summary->participantcount;
                        $submitted = $summary->submissionssubmittedcount;
                        $nothavegrade = $summary->submissionsneedgradingcount;
                        $notsubmitted = $countmaxusers - $submitted;
                        $havegrade = $submitted - $nothavegrade;

                        $segmentgray = $notsubmitted;
                        $segmentorange = $nothavegrade;
                        $segmentgreen = $havegrade;
                    }

                    $url = new moodle_url('/mod/assign/view.php', array('id' => $mod->context->instanceid, 'action' => 'grading'));

                    // Tooltip.
                    // Status Y תלמידים טרם הגישו.
                    if($notsubmitted){
                        $tooltip .='<div>' .  $notsubmitted.' '.get_string('assignnotsubmitted', 'theme_petel').' </div>';
                    }

                    // Status X תלמידים הגישו (ומחכה לבדיקה).
                    if($nothavegrade){
                        $tooltip .='<div>' . $nothavegrade . ' ' . get_string('assignsubmitted', 'theme_petel').' </div>';
                    }

                    // Status V ניתן ציון (אוטומטי או ידני).
                    if($havegrade){
                        $tooltip .= '<div>' . $havegrade.' '.get_string('assignhavegrade', 'theme_petel').' </div>';
                    }
                    break;

                case 'quiz':
                    $query = "
						SELECT qa.*
						FROM {quiz_attempts} AS qa
						LEFT JOIN {user} AS u ON(u.id = qa.userid)
						JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
						JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?)
						INNER JOIN (
							SELECT userid, MAX(attempt) AS max_attempt
							  FROM {quiz_attempts}
							  WHERE quiz = ?  
							  GROUP BY userid
						) a ON(a.userid = qa.userid AND a.max_attempt = qa.attempt)

						WHERE u.suspended = 0 AND qa.preview = 0 AND ue_d.status = 0 AND qa.quiz = ? 	
                        AND ( 
                            (ue_d.timestart = '0' AND ue_d.timeend = '0') OR 
                            (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR 
                            (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                            (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                            )
					";

                    $params = [$mod->course, $mod->instance, $mod->instance];

                    $querytmp = $query . " AND qa.state = 'inprogress' ";
                    $countinprogress = count($DB->get_records_sql($querytmp, $params));

                    $querytmp = $query . " AND qa.state = 'finished' AND sumgrades IS NOT NULL ";
                    $countwithgrades = count($DB->get_records_sql($querytmp, $params));

                    $querytmp = $query . " AND qa.state = 'finished' AND sumgrades IS NULL ";
                    $countwithoutgrades = count($DB->get_records_sql($querytmp, $params));

                    $url = new moodle_url('/mod/quiz/report.php', array('id' => $mod->context->instanceid, 'mode' => 'teacheroverview'));

                    // Gray - טרם התחיל מענה.
                    // [Blue] Gray - בתהליך.
                    // Orange - הוגש וטרם נבדק.
                    // Green - הוגש ונבדק + יש ציון.
                    // Red - איחור בהגשה.

                    $segmentorange = $countwithoutgrades;
                    $segmentgreen = $countwithgrades;

                    $segmentgray = $countmaxusers - ($countwithoutgrades + $countwithgrades);
                    $segmentblue = 0;
                    $segmentred = 0;

                    //$row = $DB->get_record('quiz', ['id' => $mod->instance]);
                    //if ($row->timeclose == 0 || $row->timeclose > time()) {
                    //    $segmentgray = $countmaxusers - ($countinprogress + $countwithoutgrades + $countwithgrades);
                    //    $segmentblue = $countinprogress;
                    //}

                    // Status טרם התחיל.
                    //$countwithoutstarted = $countmaxusers - ($countwithoutgrades + $countwithgrades + $countinprogress);
                    //if($countwithoutstarted){
                    //    $tooltip .='<div>' .  $countwithoutstarted.' '.get_string('quizwithoutstarted', 'theme_petel').' </div>';
                    //}
                    //
                    //// Status בתהליך.
                    //if($countinprogress){
                    //    $tooltip .= '<div>' . $countinprogress.' '.get_string('quizinprogress', 'theme_petel').' </div>';
                    //}

                    // Status טרם הוגש.
                    $countnosubmit = $countmaxusers - ($countwithoutgrades + $countwithgrades);
                    if($countnosubmit){
                        $tooltip .= '<div>' . $countnosubmit.' '.get_string('quiznosubmit', 'theme_petel').' </div>';
                    }

                    // Tooltip.
                    // Status הוגש X תלמידים הגישו (ומחכה לבדיקה).
                    $submitted = $countwithgrades + $countwithoutgrades;

                    if ($submitted != $countwithgrades) {
                        //if ($submitted) {
                        //    $tooltip .= '<div>' . $submitted . ' ' . get_string('quizsubmitted', 'theme_petel') . ' </div>';
                        //}

                        if ($countwithoutgrades) {
                            $tooltip .= '<div>' . $countwithoutgrades . ' ' . get_string('quizwithoutgrades', 'theme_petel') .
                                ' </div>';
                        }

                        // Status ניתן ציון.
                        if ($countwithgrades) {
                            $tooltip .= '<div>' . $countwithgrades . ' ' . get_string('quizwithgrades', 'theme_petel') . ' </div>';
                        }
                    } else {
                        if ($submitted) {
                            $tooltip .= '<div>' . $submitted . ' ' . get_string('quizsubmittedwitgrades', 'theme_petel') .
                                ' </div>';
                        }
                    }

                    break;

                case 'hvp':

                    $hvp = $DB->get_record_sql(
                        "SELECT h.id,
                                h.name AS title,
                                hl.machine_name,
                                hl.major_version,
                                hl.minor_version
                            FROM {hvp} h
                            JOIN {hvp_libraries} hl ON hl.id = h.main_library_id
                            WHERE h.id = ?", [$mod->instance]);


                    $query = "
                        SELECT u.id,
                           i.id AS gradeitemid,
                           g.id AS gradeid,
                           u.firstnamephonetic, u.lastnamephonetic, 
                           u.middlename, u.alternatename, u.firstname, u.lastname, 
                           g.rawgrade,
                           g.rawgrademax,
                           g.timemodified,
                           x.id as xapiid
                       FROM {grade_items} i 
                       LEFT JOIN {grade_grades} g ON i.id = g.itemid 
                       LEFT JOIN {user} u ON u.id = g.userid
                       LEFT JOIN {hvp_xapi_results} x ON g.userid = x.user_id
                       WHERE i.iteminstance = ? AND x.content_id = ? AND i.itemtype = 'mod' AND i.itemmodule = 'hvp' AND x.parent_id IS NULL
                       GROUP BY i.id, g.id, u.id, i.iteminstance, x.id
                       ORDER BY g.timemodified DESC                    
                    ";

                    $result = $DB->get_records_sql($query, [$mod->instance, $hvp->id]);

                    $havegrade = count($result);
                    $notsubmitted = $countmaxusers - $havegrade;

                    // Gray - טרם הוגש.
                    // Green - הוגש וניתן ציון.

                    $segmentgray = $notsubmitted;
                    $segmentgreen = $havegrade;

                    $url = new moodle_url('/mod/hvp/grade.php', array('id' => $mod->context->instanceid));

                    // Tooltip.
                    // Status Y תלמידים טרם הגישו.
                    if($notsubmitted){
                        $tooltip .='<div>' .  $notsubmitted.' '.get_string('hvpnotsubmitted', 'theme_petel').' </div>';
                    }

                    // Status V ניתן ציון (אוטומטי או ידני).
                    if($havegrade){
                        $tooltip .= '<div>' . $havegrade.' '.get_string('hvphavegrade', 'theme_petel').' </div>';
                    }
                    break;
            }

            $data                          = array();
            $data['url']                   = $url;
            $data['countmaxusers']         = $countmaxusers;

            $data['segment_green']         = $segmentgreen;
            $data['segment_green_percent'] = ($countmaxusers > 0) ? $segmentgreen / $countmaxusers * 100 : 0;

            $data['segment_orange']           = $segmentorange;
            $data['segment_orange_percent']   = ($countmaxusers > 0) ? $segmentorange / $countmaxusers * 100 : 0;

            $data['segment_blue']           = $segmentblue;
            $data['segment_blue_percent']   = ($countmaxusers > 0) ? $segmentblue / $countmaxusers * 100 : 0;

            $data['segment_gray']             = $segmentgray;
            $data['segment_gray_percent']     = ($countmaxusers > 0) ? $segmentgray / $countmaxusers * 100 : 0;

            $data['segment_red']             = $segmentred;
            $data['segment_red_percent']     = ($countmaxusers > 0) ? $segmentred / $countmaxusers * 100 : 0;

            $data['tooltip']                    = $tooltip;
        }
    }

    return $data;
}

/**
 * Get activity submission status
 * @param cm_info $mod
 * @return array|boolean
 */
function petel_cm_submission_status(cm_info $mod) {
    global $DB, $USER, $CFG;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    if (!in_array($mod->modname, ['quiz', 'assign', 'questionnaire'])) {
        return false;
    }

    $extra = $DB->get_record($mod->modname, ['id' => $mod->instance]);
    if(!$extra) {
        return false;
    }

    // Defailt result object.
    $tmod = new \stdClass();
    $tmod->duedate = 0;
    $tmod->cutoffdate = 0;
    $tmod->submitted = false;
    $tmod->requiregrade = false;
    $tmod->grade = false;
    $tmod->viewgrade = false;
    $tmod->reopened = false;
    $tmod->modstatus = '';
    $tmod->modstyle = 'text-secondary';

    // Prepare data.
    switch ($mod->modname) {
        case 'quiz':
            $tmod->duedate = $extra->timeopen;
            $tmod->cutoffdate = $extra->timeclose;
            $tmod->requiregrade = true;

            $sql = "
                SELECT *, qa.id as attemptid
                FROM {quiz_attempts} as qa
                LEFT JOIN {user} as u ON(u.id = qa.userid)
                JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?)                 
                WHERE qa.quiz = ? AND qa.userid = ? AND qa.state = 'finished' AND u.suspended = 0 AND ue_d.status = 0
                AND ( 
                    (ue_d.timestart = '0' AND ue_d.timeend = '0') OR 
                    (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR 
                    (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                    (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                    )            
            ";

            if ($rowas = $DB->get_records_sql($sql, [$mod->course, $mod->instance, $USER->id])) {

                $row = reset($rowas);

                $attemptid = $row->attemptid;
                $cmid = $mod->get_course_module_record()->id;
                $attemptobj = quiz_create_attempt_handling_errors($attemptid, $cmid);
                $attemptobj->preload_all_attempt_step_users();
                $options = $attemptobj->get_display_options(true);

                $tmod->viewgrade = ($options->attempt == 1) ? true : false;
                $tmod->submitted = true;
            }

            $sql = "
                SELECT *
                FROM {quiz_grades} as qq
                LEFT JOIN {user} as u ON(u.id = qq.userid)
                JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?)                  
                WHERE qq.quiz = ? AND qq.userid = ? AND u.suspended = 0 AND ue_d.status = 0
                AND ( 
                    (ue_d.timestart = '0' AND ue_d.timeend = '0') OR 
                    (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR 
                    (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                    (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                    )             
            ";

            if ($rowag = $DB->get_record_sql($sql, [$mod->course, $mod->instance, $USER->id])) {
                $tmod->grade = $rowag->grade;
                $tmod->submitted = true;
            }
            break;

        case 'assign':
            $tmod->duedate = $extra->allowsubmissionsfromdate;
            $tmod->cutoffdate = $extra->duedate; // $extra->cutoffdate
            $tmod->requiregrade = true;

            list ($course, $cm) = get_course_and_cm_from_cmid($mod->id, 'assign');
            $context = \context_module::instance($cm->id);
            $assign = new \assign($context, $cm, $course);

            $submission = $assign->get_user_submission($USER->id, 0);
            $status = $assign->get_grading_status($USER->id);

            if ($submission->status === 'submitted') {
                $tmod->submitted = true;
            }

            if ($status === 'notgraded' && !in_array($submission->status, ['new', 'submitted'])) {
                $tmod->submitted = false;
                $tmod->grade = false;
                $tmod->reopened = true;
            }

            if ($submission->status !== 'new' && $status !== 'notgraded') {
                $tmod->submitted = true;
                $tmod->grade = $assign->get_grade_item()->get_final($USER->id)->finalgrade;
            }
            break;

        case 'questionnaire':
            $tmod->duedate = $extra->opendate;
            $tmod->cutoffdate = $extra->closedate;
            $tmod->requiregrade = true;

            $rowas = $DB->get_record('questionnaire_response', [
                    'questionnaireid' => $extra->id,
                    'userid' => $USER->id,
                    'complete' => 'y']);
            if ($rowas) {
                $tmod->submitted = true;
                if (!empty($rowas->grade)) {
                    $tmod->grade = $rowas->grade;
                }
            }
            break;

        case 'hvp':
            $tmod->duedate = $extra->opendate;
            $tmod->cutoffdate = $extra->closedate;
            $tmod->requiregrade = true;

            $hvp = $DB->get_record_sql(
                    "SELECT h.id,
                                h.name AS title,
                                hl.machine_name,
                                hl.major_version,
                                hl.minor_version
                            FROM {hvp} h
                            JOIN {hvp_libraries} hl ON hl.id = h.main_library_id
                            WHERE h.id = ?", [$mod->instance]);


            $query = "
                        SELECT u.id,
                           i.id AS gradeitemid,
                           g.id AS gradeid,
                           u.firstnamephonetic, u.lastnamephonetic, 
                           u.middlename, u.alternatename, u.firstname, u.lastname, 
                           g.rawgrade,
                           g.rawgrademax,
                           g.timemodified,
                           x.id as xapiid
                       FROM {grade_items} i 
                       LEFT JOIN {grade_grades} g ON i.id = g.itemid 
                       LEFT JOIN {user} u ON u.id = g.userid
                       LEFT JOIN {hvp_xapi_results} x ON g.userid = x.user_id
                       WHERE i.iteminstance = ? AND x.content_id = ? AND i.itemtype = 'mod' AND i.itemmodule = 'hvp' AND x.parent_id IS NULL AND u.id=?
                       GROUP BY i.id, g.id, u.id, i.iteminstance, x.id
                       ORDER BY g.timemodified DESC                    
                    ";

            $rowas = $DB->get_record_sql($query, [$mod->instance, $hvp->id, $USER->id]);
            if ($rowas && !empty($rowas->rawgrade)) {
                $tmod->submitted = true;
                $tmod->grade = $rowas->rawgrade;
            }
            break;
    }

    // Check teacher.
    $isteacher = false;
    if (is_siteadmin() || has_capability('moodle/course:update', context_module::instance($mod->id))) {
        $isteacher = true;
    }

    if($isteacher){
        $tmod->modstatus = ($tmod->cutoffdate) ? date("d/m/Y H:i", $tmod->cutoffdate) : get_string('no_submission_date', 'theme_petel');
        //$tmod->modstyle = ($tmod->cutoffdate && $tmod->cutoffdate < time()) ? 'text-danger' : 'text-secondary';
    }else{
        // Student.
        // Status הוגש וניתן ציון.
        if($tmod->submitted && $tmod->requiregrade && $tmod->grade){

            if($tmod->viewgrade){
                $tmod->modstatus = get_string('complete', 'theme_petel').' ('.ceil($tmod->grade).')';
            }else{
                $tmod->modstatus = get_string('complete', 'theme_petel');
            }

            $tmod->modstyle = 'text-success';
        }

        // Status הוגש ואין הגדרת ציון.
        if($tmod->submitted && !$tmod->requiregrade){
            $tmod->modstatus = get_string('complete', 'theme_petel');
            $tmod->modstyle = 'text-success';
        }

        // Status הוגש וטרם נבדק.
        if($tmod->submitted && $tmod->requiregrade && !$tmod->grade){
            $tmod->modstatus = get_string('waitgrade', 'theme_petel');
            $tmod->modstyle = 'text-secondary';
        }

        // Status טרם התחיל.
        if(!$tmod->submitted && $tmod->cutoffdate && $tmod->cutoffdate > time()){
            $delta = petel_convert_seconds_to_time($tmod->cutoffdate - time());

            // Date 4 ימים ויותר להגשה.
            if($delta->days >= 4){
                $a = new stdClass();
                $a->date = date("d/m/Y H:i", $tmod->cutoffdate);
                $tmod->modstatus = get_string('cut_of_date_label', 'theme_petel', $a);
            }

            // Date במהלך 3 הימים האחרונים.
            if($delta->days > 0 && $delta->days < 4){
                $a = $delta->days.' '.get_string('days').get_string('and', 'theme_petel').
                        $delta->hours.' '.get_string('hours');
                $tmod->modstatus = get_string('cut_of_date_less_days_label', 'theme_petel', $a);
            }

            // Date במהלך היום האחרון.
            if($delta->days == 0){
                $a = $delta->hours.' '.get_string('hours');
                $tmod->modstatus = get_string('cut_of_date_less_days_label', 'theme_petel', $a);
            }

            $tmod->modstyle = 'text-secondary';
        }

        // Status ללא תאריך הגשה.
        if(!$tmod->submitted && $tmod->cutoffdate == 0){
            $tmod->modstatus = get_string('no_submission_date', 'theme_petel');
            $tmod->modstyle = 'text-secondary';
        }

        // Status לאחר תאריך הגשה סופי.
        if(!$tmod->submitted && $tmod->cutoffdate && $tmod->cutoffdate <= time()){
            $tmod->modstatus = get_string('cut_of_date', 'theme_petel');
            $tmod->modstyle = 'text-danger';
        }
    }

    return ['modstatus' => $tmod->modstatus, 'modstyle' => $tmod->modstyle];
}

/**
 * @param $seconds
 * @return stdClass
 */
function petel_convert_seconds_to_time($seconds) {

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // Extract days.
    $days = floor($seconds / $secondsInADay);

    // Extract hours.
    $hourSeconds = $seconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes.
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds.
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Return the final object.
    $obj = new \stdClass();
    $obj->days = (int) $days;
    $obj->hours = (int) $hours;
    $obj->minutes = (int) $minutes;
    $obj->seconds = (int) $seconds;

    return $obj;
}

function petel_quiz_status_attempt($mod, $userid) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    $sql = "SELECT *
            FROM {quiz_attempts} as qa
            LEFT JOIN {user} as u ON(u.id = qa.userid)
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?) 
            WHERE qa.quiz = ? AND qa.userid = ? AND u.suspended = 0 AND ue_d.status = 0
            AND ( 
                (ue_d.timestart = '0' AND ue_d.timeend = '0') OR 
                (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR 
                (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                )             
            ";

    $attempt = $DB->get_record_sql($sql, [$mod->course, $mod->instance, $userid]);
    if(!empty($attempt)){
        $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz));

        if ($attempt->state == quiz_attempt::IN_PROGRESS) {
            $state = mod_quiz_display_options::DURING;
        } else if ($quiz->timeclose && time() >= $quiz->timeclose) {
            $state = mod_quiz_display_options::AFTER_CLOSE;
        } else if (time() < $attempt->timefinish + 120) {
            $state = mod_quiz_display_options::IMMEDIATELY_AFTER;
        } else {
            $state = mod_quiz_display_options::LATER_WHILE_OPEN;
        }
    }else{
        $state = mod_quiz_display_options::LATER_WHILE_OPEN;
    }
///////////////////////////////////////////
    switch ($state) {
        case mod_quiz_display_options::DURING:
            $whenname = 'during';
            $when = mod_quiz_display_options::DURING;
            break;
        case mod_quiz_display_options::IMMEDIATELY_AFTER:
            $whenname = 'immediately';
            $when = mod_quiz_display_options::IMMEDIATELY_AFTER;
            break;
        case mod_quiz_display_options::LATER_WHILE_OPEN:
            $whenname = 'open';
            $when = mod_quiz_display_options::LATER_WHILE_OPEN;
            break;
        case mod_quiz_display_options::AFTER_CLOSE:
            $whenname = 'closed';
            $when = mod_quiz_display_options::AFTER_CLOSE;
            break;
        default:
            $whenname = '';
            $when = 0;
    }
    ///////////////////////////////////////////

    $result = new \StdClass();

    $reviewfields = array(
            'attempt'          => array('theattempt', 'quiz'),
            'correctness'      => array('whethercorrect', 'question'),
            'marks'            => array('marks', 'quiz'),
            'specificfeedback' => array('specificfeedback', 'question'),
            'generalfeedback'  => array('generalfeedback', 'question'),
            'rightanswer'      => array('rightanswer', 'question'),
            'overallfeedback'  => array('reviewoverallfeedback', 'quiz'),
    );

    foreach ($reviewfields as $field => $notused) {
        $cfgfield = 'review' . $field;

        if ($quiz !== null && $quiz->$cfgfield & $when) {
            $result->$field = 1;
        } else {
            $result->$field = 0;
        }
    }

    // Add status.
    $result->status = $whenname;

    return $result;
}

/**
 * Petel activity count users attempt
 * @param cm_info $mod
 * @return array
 */
function petel_activity_count_users_attempt($mod) {
    global $DB;

    $count    = 0;
    $cid = $DB->get_field('course_modules', 'course', ['id' => (int)$mod->id]);
    $relatedusers = petel_get_students_course($cid);
    $maxcount = count($relatedusers);
    $relatedusersids = [];
    foreach ($relatedusers as $ru) {
        $relatedusersids[] = $ru->userid;
    }

    // Quiz.
    if ($mod->modname === 'quiz') {
        $sql = "SELECT *
                FROM {quiz_attempts} as qa
                LEFT JOIN {user} as u ON(u.id = qa.userid)
                LEFT JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                LEFT JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?)
                WHERE qa.quiz=? AND qa.state='finished' AND u.suspended = 0 AND ue_d.status = 0
                AND ( 
                    (ue_d.timestart = '0' AND ue_d.timeend = '0') OR 
                    (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR 
                    (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                    (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                    ) 
                GROUP BY qa.userid;";
        $query = $DB->get_records_sql($sql, array($mod->course, $mod->instance));
        $count = count($query);
    }

    /* FIX. Field survey_id removed
    // Questionnaire.
    if ($mod->modname == 'questionnaire') {
    $sid = $DB->get_record('questionnaire', array('id' => $mod->instance), 'sid');
    $sql = "
    SELECT *
    FROM {questionnaire_response}
    WHERE survey_id=? AND complete='y'
    GROUP BY userid;
    ";

    $query = $DB->get_records_sql($sql, array($sid->sid));
    $count = count($query);
    }
     */
    // Assign.
    if ($mod->modname === 'assign') {
        $sql = "SELECT asub.*
                  FROM {assign_submission} AS asub
             LEFT JOIN {assignsubmission_file} AS asf
                       ON asf.assignment = :asfassignment
                          AND asf.submission = asub.id
                 WHERE asub.assignment=:asubassignment AND STATUS='submitted'
                       AND asf.`numfiles` IS NULL OR asf.`numfiles` > 0
              GROUP BY userid;";

        $assignparams = [];
        $assignparams['asfassignment'] = $mod->instance;
        $assignparams['asubassignment'] = $mod->instance;

        $query = $DB->get_records_sql($sql, $assignparams);

        $countusers = [];
        foreach ($query as $q) {
            if (in_array($q->userid, array_values($relatedusersids))) {
                $countusers[] = $q;
            }
        }

        $count = count($countusers);
    }

    return array('maxusers' => $maxcount, 'users' => $count);
}

/**
 * Get student users of course.
 * @param int $courseid
 * @return array
 */
function petel_get_students_course($courseid) {
    global $DB;

    $sql = "SELECT u.id AS userid, CONCAT(u.firstname,' ',u.lastname) AS name
            FROM {user} u
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?)            
            LEFT JOIN {role_assignments} ra ON ra.userid = u.id
            LEFT JOIN {context} ct ON ct.id = ra.contextid
            LEFT JOIN {course} c ON c.id = ct.instanceid
            LEFT JOIN {role} r ON r.id = ra.roleid
            WHERE r.shortname=? AND c.id=? AND u.suspended = 0 AND ue_d.status = 0
                AND ( 
                    (ue_d.timestart = '0' AND ue_d.timeend = '0') OR 
                    (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR 
                    (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                    (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                    )            
            ";
    $students = $DB->get_records_sql($sql, array($courseid, 'student', $courseid));

    return array_values($students);
}

function petel_custom_messages() {
    global $USER, $COURSE, $PAGE, $CFG, $DB;

    $cookiename = $CFG->instancename . 'custom_messages_course';
    $lifetime = 30*60;

    if($PAGE->pagetype == 'mod-quiz-attempt'){
        setcookie($cookiename, $COURSE->id, time() + $lifetime, "/");
    }

    if(in_array($PAGE->pagetype, ['mod-quiz-review'])){
        unset($_COOKIE[$cookiename]);
    }

    $courseid = !empty($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : 0;
    $course = $DB->get_record('course', ['id' => $courseid]);

    if (empty($course)) {
        return [false, []];
    }

    $context = context_course::instance($courseid);
    $users = get_enrolled_users($context, 'enrol/manual:manage');

    //Check admin or teacher on course.
    $admins = [];
    foreach (get_admins() as $admin) {
        $admins[] = $admin->id;
    }

    foreach ($users as $admin) {
        $admins[] = $admin->id;
    }

    if(in_array($USER->id, array_unique($admins))){
        return [false, []];
    }

    // Find editingteacher.
    $neededusers = [];
    foreach($users as $u){
        foreach(get_user_roles($context, $u->id, true) as $role){
            if($role->shortname == 'editingteacher'){
                $neededusers[$role->timemodified] = $role->userid;
            }
        }
    }

    krsort($neededusers);

    $d = array_values($neededusers);
    $userid = array_shift($d);

    // Prepare data.
    if(!isset($users[$userid])){
        $enable = false;
        $user = null;
    }else{
        $enable = true;
        $user = $users[$userid];
    }

    return [$enable, $user];
}