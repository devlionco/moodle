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
     * Recursion for grab subsections.
     *
     * @return array
     */
    private static function get_sub_sections_cmids(&$cmids, $sectionid): void {
        global $DB;

        if ($obj = $DB->get_record('course_sections', ['id' => $sectionid])) {
            $cmids = array_merge($cmids, explode(',', $obj->sequence));
            $modinfo = get_fast_modinfo($obj->course);

            // Subsections.
            foreach ($modinfo->get_section_info_all() as $num => $subsection) {
                if ($subsection->parent == $obj->section && $num != $obj->section) {
                    self::get_sub_sections_cmids($cmids, $subsection->id);
                }
            }

            $cmids = array_filter($cmids);
            $cmids = array_unique($cmids);
        }
    }

    /**
     * Returns welcome message
     * @param int $sectionid
     * @return string
     */
    public static function get_section_status($sectionid) {
        global $DB;

        $data = $cmids = [];
        $waitingforsubmission = $failed = $notsubmitted = 0;

        self::get_sub_sections_cmids($cmids, $sectionid);

        if ($obj = $DB->get_record('course_sections', ['id' => $sectionid])) {
            $modinfo = get_fast_modinfo($obj->course);

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
        }

        if($waitingforsubmission > 0){
            $data['waitingforsubmission'] = ['value' => $waitingforsubmission];
        }
        if($failed > 0){
            $data['failed'] = ['value' => $failed];
        }
        if($notsubmitted > 0){
            $data['notsubmitted'] = ['value' => $notsubmitted];
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
