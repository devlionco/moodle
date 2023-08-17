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
 * External Web Service class
 *
 * @package    format_flexsections
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . "/externallib.php";
require_once $CFG->dirroot . "/course/format/flexsections/lib.php";
require_once $CFG->dirroot . "/course/format/flexsections/locallib.php";

/**
 * External functions for theme petel.
 *
 * @package     format_flexsections
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_flexsections_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function change_courseimage_parameters() {
        return new external_function_parameters(
            array(
                'img'      => new external_value(PARAM_TEXT, 'image in base64'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'filename' => new external_value(PARAM_TEXT, 'filename'),
            )
        );
    }

    /**
     * Returns welcome message
     * @param string $img
     * @param int $sectionid
     * @param string $filename
     * @return string
     */
    public static function change_courseimage($img, $courseid, $filename) {

        $filename = str_replace(' ', '_', $filename);

        preg_match('/^data:image\/(\w+);base64,/', $img, $type);
        $img  = substr($img, strpos($img, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new \Exception('invalid image type');
        }
        $img = str_replace(' ', '+', $img);
        $img = base64_decode($img);

        if ($img === false) {
            return json_encode(['url' => '']);
        }

        $context = context_course::instance($courseid);

        $fs = get_file_storage();

        // Prepare file record object
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'course',
            'filearea'  => 'overviewfiles',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $filename,
        );

        $fs->delete_area_files($context->id, 'course', 'overviewfiles');
        $fs->create_file_from_string($fileinfo, $img);

        $course = get_course($courseid);

        $image = \cache::make('core', 'course_image');
        $image->delete($course->id);

        $courseimage  = format_flexsections_get_course_image($course);

        $data['url'] = $courseimage;

        return json_encode($data);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function change_courseimage_returns() {
        return new external_value(PARAM_RAW, 'Answer to the front');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function change_sectionimage_parameters() {
        return new external_function_parameters(
            array(
                'img'      => new external_value(PARAM_TEXT, 'image in base64'),
                'sectionid' => new external_value(PARAM_INT, 'section id'),
                'filename' => new external_value(PARAM_TEXT, 'filename'),
            )
        );
    }

    /**
     * Returns welcome message
     * @param string $img
     * @param int $sectionid
     * @param string $filename
     * @return string
     */
    public static function change_sectionimage($img, $sectionid, $filename) {
        global $DB, $CFG;

        $filename = str_replace(' ', '_', $filename);

        $row = $DB->get_record('course_sections', ['id' => $sectionid]);

        preg_match('/^data:image\/(\w+);base64,/', $img, $type);
        $img  = substr($img, strpos($img, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new \Exception('invalid image type');
        }

        $img = str_replace(' ', '+', $img);
        $img = base64_decode($img);

        if ($img === false) {
            return json_encode(['url' => '']);
        }

        $context = context_course::instance($row->course);

        $fs = get_file_storage();

        // Prepare file record object.
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'format_flexsections',
            'filearea'  => 'image',
            'itemid'    => $sectionid,
            'filepath'  => '/',
            'filename'  => $filename,
        );

        $fs->delete_area_files($context->id, 'format_flexsections', 'image', $sectionid);
        $file = $fs->create_file_from_string($fileinfo, $img);

        $url = $CFG->wwwroot . "/pluginfile.php/". $file->get_contextid() . '/' . $file->get_component() . '/' .
            $file->get_filearea() . '/' . $file->get_itemid() . $file->get_filepath() . $file->get_filename();

        $data['url'] = $url;

        return json_encode($data);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function change_sectionimage_returns() {
        return new external_value(PARAM_RAW, 'Answer to the front');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_activity_grade_status_parameters() {
        return new external_function_parameters(
            array(
                'cmids' => new external_value(PARAM_RAW, 'Activity ID'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
            )
        );
    }

    /**
     * Returns get_activity_grade_status
     * @return string
     */
    public static function get_activity_grade_status($cmids, $courseid) {
        global $CFG, $OUTPUT, $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);

        $params = self::validate_parameters(self::get_activity_grade_status_parameters(),
                array(
                        'cmids' => $cmids,
                        'courseid' => (int) $courseid,
                )
        );

        $result = [];
        foreach(json_decode($cmids) as $cmid) {
            $modinfo = get_fast_modinfo($params['courseid']);
            $cm = $modinfo->get_cm($cmid);

            // Grade status.
            $data = [];
            $data['gradestatus'] = format_flexsections_cm_grade_status($cm);
            $data['submissionstatus'] = format_flexsections_cm_submission_status($cm);
            $gradehtml = $OUTPUT->render_from_template('format_flexsections/activity_grade', $data);

            // Check version updated of oercatalog activity.
            $data = [];
            list($status, $description) = \community_oer\reviews_oer::check_version_of_oercatalog_activity($cmid);
            $data['show_version_updated_btn'] = $status;
            $data['version_updated_btn_description'] = $description;
            $oerversionhtml = $OUTPUT->render_from_template('format_flexsections/activity_version_update', $data);

            $result[] = ['cmid' => $cmid, 'gradestatus' => $gradehtml, 'oerversion' => $oerversionhtml];
        }

        return json_encode(['result' => $result]);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_activity_grade_status_returns() {
        return new external_value(PARAM_RAW, 'Activity grade status');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_section_status_parameters() {
        return new external_function_parameters(
            array(
                'sectionid' => new external_value(PARAM_INT, 'section id'),
            )
        );
    }

    /**
     * Returns welcome message
     * @param int $sectionid
     * @return string
     */
    public static function get_section_status($sectionid) {
        global $DB, $USER;

        $data = $cmids = [];
        format_flexsections_get_sub_sections_cmids($cmids, $sectionid);

        $section = $DB->get_record('course_sections', ['id' => $sectionid]);
        $modinfo = get_fast_modinfo($section->course);

        // Get last access activity. PTL-9737.
        if (!empty($cmids)) {
            $sql = "
                SELECT *
                FROM {logstore_standard_log}
                WHERE `action`='viewed' AND `target`='course_module' AND `contextinstanceid` IN (".implode(',', $cmids).") AND userid=?
                ORDER BY `timecreated` DESC
                LIMIT 1
                ;
            ";

            if ($log = $DB->get_record_sql($sql, [$USER->id])) {

                $cm = $modinfo->get_cm($log->contextinstanceid);

                $data['cmlastaccess'] = [
                        'cmid' => $log->contextinstanceid,
                        'cmname' => $cm->name,
                        'cmurl' => $cm->url->out(),
                ];
            }
        }

        // Teacher.
        if(format_flexsections_has_teacher_course_capability($section->course)){

            $students = [];
            $context = \context_course::instance($section->course);
            foreach (get_enrolled_users($context) as $enroluser) {
                foreach (get_user_roles($context, $enroluser->id, true) as $role) {
                    if ($role->shortname == 'student') {
                        $students[] = $enroluser->id;
                    }
                }
            }

            $cmwaitingforsubmission = $cmfailed = $cmnotsubmitted = 0;
            foreach ($cmids as $cmid){
                try {
                    $cm = $modinfo->get_cm($cmid);
                    $flagwaitingforsubmission = $flagfailed = $flagnotsubmitted = false;
                    foreach ($students as $userid) {
                        if ($tmod = format_flexsections_cm_submission_data($cm, $userid)) {
                            // Status הוגש וטרם נבדק.
                            if ($tmod->submitted && $tmod->requiregrade && !$tmod->grade) {
                                $flagwaitingforsubmission = true;
                            }

                            // Status failed.
                            if ($tmod->failed) {
                                $flagfailed = true;
                            }

                            // Status לאחר תאריך הגשה סופי.
                            if (!$tmod->submitted && $tmod->cutoffdate && $tmod->cutoffdate <= time()) {
                                $flagnotsubmitted = true;
                            }
                        }
                    }

                    if ($flagwaitingforsubmission) {
                        $cmwaitingforsubmission++;
                    }
                    if ($flagfailed) {
                        $cmfailed++;
                    }
                    if ($flagnotsubmitted) {
                        $cmnotsubmitted++;
                    }
                } catch (Exception $e) {

                }
            }

            if($cmwaitingforsubmission > 0){
                $data['firstrow'] = [
                        'value' => $cmwaitingforsubmission,
                        'label' => get_string('statuscmwaitingforsubmission', 'format_flexsections')
                ];
            }
            if($cmfailed > 0){
                $data['secondrow'] = [
                        'value' => $cmfailed,
                        'label' => get_string('statuscmfailed', 'format_flexsections')
                ];
            }
        }else{
            // Student.
            $waitingforsubmission = $failed = $notsubmitted = 0;

            foreach ($cmids as $cmid){
                $cm = $modinfo->get_cm($cmid);
                if ($tmod = format_flexsections_cm_submission_data($cm)) {

                    // Status הוגש וטרם נבדק.
                    if ($tmod->submitted && $tmod->requiregrade && !$tmod->grade) {
                        $waitingforsubmission++;
                    }

                    // Status failed.
                    if ($tmod->failed) {
                        $failed++;
                    }

                    // Status לאחר תאריך הגשה סופי.
                    if (!$tmod->submitted && $tmod->cutoffdate && $tmod->cutoffdate <= time()) {
                        $notsubmitted++;
                    }
                }
            }

            if($waitingforsubmission > 0){
                $data['firstrow'] = [
                        'value' => $waitingforsubmission,
                        'label' => get_string('statuswaitingforsubmission', 'format_flexsections')
                ];
            }
            if($failed > 0){
                $data['secondrow'] = [
                        'value' => $failed,
                        'label' => get_string('statusfailed', 'format_flexsections')
                ];
            }
            if($notsubmitted > 0){
                $data['thirdrow'] = [
                        'value' => $notsubmitted,
                        'label' => get_string('statusnotsubmittedintime', 'format_flexsections')
                ];
            }
        }

        return json_encode($data);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_section_status_returns() {
        return new external_value(PARAM_RAW, 'Answer to section status');
    }

}
