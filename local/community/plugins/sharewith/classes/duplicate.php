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
 * External course API
 *
 * @package    core_course
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/local/community/plugins/sharewith/locallib.php');

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class duplicate extends \external_api {

    private $modulemetadata;
    private $glossarycopyusers;
    private $databasecopyusers;
    private $copytype;
    private $objduplicate;

    public function __construct() {
        global $CFG;

        $this->modulemetadata = 70;
        $this->glossarycopyusers = false;
        $this->databasecopyusers = false;
        $this->copytype = '';
        $this->objduplicate = null;

    }

    public function enable_glossary_copy_users() {
        $this->glossarycopyusers = true;
    }

    public function enable_database_copy_users() {
        $this->databasecopyusers = true;
    }

    public function set_copy_type($str) {
        $this->copytype = $str;
    }

    public function get_copy_type() {
        return $this->copytype;
    }

    public function set_obj_duplicate($obj) {
        $this->objduplicate = $obj;
    }

    public function get_obj_duplicate() {
        return $this->objduplicate;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function duplicate_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course to duplicate id'),
                        'fullname' => new external_value(PARAM_TEXT, 'duplicated course full name'),
                        'shortname' => new external_value(PARAM_TEXT, 'duplicated course short name'),
                        'categoryid' => new external_value(PARAM_INT, 'duplicated course category parent'),
                        'visible' => new external_value(PARAM_INT, 'duplicated course visible, default to yes', VALUE_DEFAULT, 1),
                        'options' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'name' => new external_value(PARAM_ALPHAEXT, 'The backup option name:
                                            "activities" (int) Include course activites (default to 1 that is equal to yes),
                                            "blocks" (int) Include course blocks (default to 1 that is equal to yes),
                                            "filters" (int) Include course filters  (default to 1 that is equal to yes),
                                            "users" (int) Include users (default to 0 that is equal to no),
                                            "enrolments" (int) Include enrolment methods (default to 1 - restore only with users),
                                            "role_assignments" (int) Include role assignments  (default to 0 that is equal to no),
                                            "comments" (int) Include user comments  (default to 0 that is equal to no),
                                            "userscompletion" (int) Include user course completion information  (default to 0 that is equal to no),
                                            "logs" (int) Include course logs  (default to 0 that is equal to no),
                                            "grade_histories" (int) Include histories  (default to 0 that is equal to no)'
                                                ),
                                                'value' => new external_value(PARAM_RAW,
                                                        'the value for the option 1 (yes) or 0 (no)'
                                                )
                                        )
                                ), VALUE_DEFAULT, array()
                        ),
                )
        );
    }

    /**
     * Duplicate a course
     *
     * @param int $courseid
     * @param string $fullname Duplicated course fullname
     * @param string $shortname Duplicated course shortname
     * @param int $categoryid Duplicated course parent category id
     * @param int $visible Duplicated course availability
     * @param array $options List of backup options
     * @return array New course info
     * @since Moodle 2.3
     */
    public static function duplicate_course($userid, $courseid, $fullname, $shortname, $categoryid, $visible = 1,
            $options = array()) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Parameter validation.
        $params = self::validate_parameters(
                self::duplicate_course_parameters(),
                array(
                        'courseid' => $courseid,
                        'fullname' => $fullname,
                        'shortname' => $shortname,
                        'categoryid' => $categoryid,
                        'visible' => $visible,
                        'options' => $options
                )
        );

        // Context validation.
        if (!($course = $DB->get_record('course', array('id' => $params['courseid'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }

        // Category where duplicated course is going to be created.
        $categorycontext = context_coursecat::instance($params['categoryid']);
        self::validate_context($categorycontext);

        // Course to be duplicated.
        $coursecontext = context_course::instance($course->id);
        self::validate_context($coursecontext);

        //'enrolments' => backup::ENROL_WITHUSERS - default
        //ENROL_NEVER - Backup a course with enrolment methods and restore it without user data
        //ENROL_WITHUSERS - Backup a course with enrolment methods and restore it with user data with enrolment methods
        //ENROL_ALWAYS - Backup a course with enrolment methods and restore it without user data with enrolment methods

        $backupdefaults = array(
                'activities' => 1,
                'blocks' => 1,
                'filters' => 1,
                'users' => 0,
                'enrolments' => backup::ENROL_NEVER,
                'role_assignments' => 0,
                'comments' => 0,
                'userscompletion' => 0,
                'logs' => 0,
                'grade_histories' => 0
        );

        $backupsettings = array();
        // Check for backup and restore options.
        if (!empty($params['options'])) {
            foreach ($params['options'] as $option) {

                // Strict check for a correct value (allways 1 or 0, true or false).
                $value = clean_param($option['value'], PARAM_INT);

                if ($value !== 0 && $value !== 1) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                if (!isset($backupdefaults[$option['name']])) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                $backupsettings[$option['name']] = $value;
            }
        }

        // Check if the shortname is used.
        if ($foundcourses = $DB->get_records('course', array('shortname' => $shortname))) {
            foreach ($foundcourses as $foundcourse) {
                $foundcoursenames[] = $foundcourse->fullname;
            }

            $foundcoursenamestring = implode(',', $foundcoursenames);
            throw new moodle_exception('shortnametaken', '', '', $foundcoursenamestring);
        }

        // Backup the course.

        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userid);

        foreach ($backupsettings as $name => $value) {
            if ($setting = $bc->get_plan()->get_setting($name)) {
                $bc->get_plan()->get_setting($name)->set_value($value);
            }
        }

        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];

        $bc->destroy();

        // Restore the backup immediately.

        // Check if we need to unzip the file because the backup temp dir does not contains backup files.
        if (!file_exists($backupbasepath . "/moodle_backup.xml")) {
            $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        }

        // Create new course.
        $newcourseid = restore_dbops::create_new_course($params['fullname'], $params['shortname'], $params['categoryid']);

        $rc = new restore_controller($backupid, $newcourseid,
                backup::INTERACTIVE_NO, backup::MODE_HUB, $userid, backup::TARGET_NEW_COURSE);

        foreach ($backupsettings as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                $setting->set_value($value);
            }
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';
                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
        $course->fullname = $params['fullname'];
        $course->shortname = $params['shortname'];
        $course->visible = $params['visible'];

        // Set shortname and fullname back.
        $DB->update_record('course', $course);

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        // Delete the course backup file created by this WebService. Originally located in the course backups area.
        $file->delete();

        // Copy BADGES from source course.
        $newcourseid = $course->id;

        $context = context_course::instance($courseid);
        $newcontext = context_course::instance($newcourseid);
        $badges = $DB->get_records('badge', array('courseid' => $courseid));
        foreach ($badges as $badge) {
            $newbadge = clone $badge;

            // Insert new badge.
            unset($newbadge->id);
            $newbadge->courseid = $newcourseid;
            $newbadgeid = $DB->insert_record('badge', $newbadge);

            // Copy badge file.
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'badges', 'badgeimage', $badge->id);

            // Create files.
            foreach ($files as $f) {
                if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                    $fileinfo = array(
                            'contextid' => $newcontext->id,
                            'component' => $f->get_component(),
                            'filearea' => $f->get_filearea(),
                            'itemid' => $newbadgeid,
                            'filepath' => $f->get_filepath(),
                            'filename' => $f->get_filename()
                    );

                    // Save file.
                    $fs->create_file_from_string($fileinfo, $f->get_content());
                }
            }

            $criterias = $DB->get_records('badge_criteria', array('badgeid' => $badge->id));
            foreach ($criterias as $criteria) {
                $newcriteria = clone $criteria;

                // Insert new criteria.
                unset($newcriteria->id);
                $newcriteria->badgeid = $newbadgeid;
                $newcriteriaid = $DB->insert_record('badge_criteria', $newcriteria);

                $criteriaparams = $DB->get_records('badge_criteria_param', array('critid' => $criteria->id));
                foreach ($criteriaparams as $criteriaparam) {
                    $newcriteriaparam = clone $criteriaparam;

                    // Insert new criteria param.
                    unset($newcriteriaparam->id);
                    $newcriteriaparam->critid = $newcriteriaid;

                    if ($newcriteriaparam->name == 'course_' . $courseid) {
                        $newcriteriaparam->name = 'course_' . $newcourseid;
                        $newcriteriaparam->value = $newcourseid;
                    }

                    $newcriteriaparamid = $DB->insert_record('badge_criteria_param', $newcriteriaparam);
                }
            }
        }

        return array('id' => $course->id, 'shortname' => $course->shortname);
    }

    /**
     * Duplicates activity
     *
     * @param int $sourceactivityid source
     * @param int $courseid target
     * @param int $sectionid target
     * @return cm_info|null cminfo object if we sucessfully duplicated the mod and found the new cm.
     */
    public function duplicate_activity_source($sourceactivityid, $courseid, $sectionid, &$newactivityid) {
        global $USER, $DB, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Copy competencies to target course.
        community_sharewith_copy_competencies($sourceactivityid, $courseid);

        $bc = new \backup_controller(backup::TYPE_1ACTIVITY, $sourceactivityid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
                backup::MODE_IMPORT, $USER->id);
        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();
        $bc->execute_plan();
        $bc->destroy();

        $rc = new \restore_controller($backupid, $courseid, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id,
                backup::TARGET_CURRENT_ADDING);
        $cmcontext = context_module::instance($sourceactivityid);
        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }
            }
        }

        $rc->execute_plan();

        $newcmid = null;
        $tasks = $rc->get_plan()->get_tasks();
        foreach ($tasks as $task) {
            if (is_subclass_of($task, 'restore_activity_task')) {
                if ($task->get_old_contextid() == $cmcontext->id) {
                    $newcmid = $task->get_moduleid();
                    break;
                }
            }
        }

        if ($newcmid) {
            $course = get_course($courseid);
            $info = get_fast_modinfo($course);
            $newcm = $info->get_cm($newcmid);
            $section = $DB->get_record('course_sections', array('id' => $sectionid, 'course' => $courseid));
            moveto_module($newcm, $section);
        }

        // Copy users for mod_database, mod_glossary, mod_game.
        if ($this->databasecopyusers) {
            community_sharewith_copy_users_mod_database($sourceactivityid, $newcmid);
        }

        // PTL-1253 Multiple backup and restores (nadavkav).
        if ($this->glossarycopyusers) {
            community_sharewith_copy_users_mod_glossary($sourceactivityid, $newcmid);
        }

        $objgame = $this->copy_mod_game($sourceactivityid, $newcm, $courseid, $sectionid);
        if (!empty($objgame)) {
            $newactivityid[] = $objgame;
        }

        // PTL-2115 Insert history_cmid and history_authors.
        community_sharewith_update_meta_history($sourceactivityid, $newcmid, $this->objduplicate);

        rebuild_course_cache($newcm->course);
        $rc->destroy();
        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        $newactivityid[] = $newcm;

        return isset($newcmid) ? $newcmid : null;
    }

    /**
     * Duplicates activity
     *
     * @param int $sourceactivityid source
     * @param int $courseid target
     * @param int $sectionid target
     * @return array
     */
    public function duplicate_activity($sourceactivityid, $courseid, $sectionid, &$newactivityid, $activitysequence = false) {
        global $DB;

        $newcmids = array();

        if (!empty($activitysequence) && is_array($activitysequence)) {
            $activitysequence = array_reverse($activitysequence);
            $chain = array();

            foreach ($activitysequence as $cmid) {
                $newcmid = $this->duplicate_activity_source($cmid, $courseid, $sectionid, $newactivityid);
                $newcmids[$cmid] = $newcmid;
                $chain[$cmid] = $newcmid;

                $newcm = $DB->get_record('course_modules', array('id' => $newcmid));
                $cm = $DB->get_record('course_modules', array('id' => $cmid));

                if (!empty($cm->availability)) {
                    $availability = json_decode($cm->availability);
                    foreach ($availability->c as $key => $item) {
                        if (array_key_exists($item->cm, $chain)) {
                            $availability->c[$key]->cm = (int) $chain[$item->cm];
                        } else {
                            unset($availability->c[$key]);
                            unset($availability->showc[$key]);
                        }
                    }
                    $availability->c = array_values($availability->c);
                    $availability->showc = array_values($availability->showc);
                    $newcm->availability = json_encode($availability);
                    $res = $DB->update_record('course_modules', $newcm);
                }
            }

            rebuild_course_cache($courseid);
        } else {
            $row = $DB->get_record('course_modules', array('id' => $sourceactivityid));
            $newcmids[$sourceactivityid] =
                    $this->duplicate_activity_source($sourceactivityid, $courseid, $sectionid, $newactivityid);
            $DB->update_record('course_modules', $row);
        }

        return $newcmids;
    }

    /**
     * Duplicates activity
     *
     * @param int $sourcesectionid source
     * @param int $courseid target
     * @return section_info|null section object if we sucessfully duplicated the section and found the new cm.
     */
    public function duplicate_section($sourcesectionid, $courseid) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $sourcesection = $DB->get_record('course_sections', array('id' => $sourcesectionid));
        $newsection = course_create_section($courseid, 0);

        // Add default name and summary for section.
        if ($sourcesection->name != null || $sourcesection->summary != null) {
            $newsection->name = $sourcesection->name;
            $newsection->summary = $sourcesection->summary;
            $DB->update_record('course_sections', $newsection);
        }

        // Copy files.
        $oldcontext = \context_course::instance($sourcesection->course);
        $newcontext = \context_course::instance($newsection->course);

        $fs = get_file_storage();
        $files = $fs->get_area_files($oldcontext->id, 'course', 'section', $sourcesection->id);

        // Create files.
        foreach ($files as $f) {
            if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                $fileinfo = array(
                        'contextid' => $newcontext->id,
                        'component' => $f->get_component(),
                        'filearea' => $f->get_filearea(),
                        'itemid' => $newsection->id,
                        'filepath' => $f->get_filepath(),
                        'filename' => $f->get_filename()
                );

                // Save file.
                $fs->create_file_from_string($fileinfo, $f->get_content());
            }
        }

        // Copy flexsections image.
        $files = $fs->get_area_files($oldcontext->id, 'format_flexsections', 'image', $sourcesection->id);
        foreach ($files as $f) {
            if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                $filename = str_replace(' ', '_', $f->get_filename());
                $fileinfo = array(
                    'contextid' => $newcontext->id,
                    'component' => $f->get_component(),
                    'filearea' => $f->get_filearea(),
                    'itemid' => $newsection->id,
                    'filepath' => $f->get_filepath(),
                    'filename' => $filename
                );

                // Save file.
                $fs->create_file_from_string($fileinfo, $f->get_content());
            }
        }

        $arrcmids = [];
        $activities = explode(',', $sourcesection->sequence);
        foreach ($activities as $key => $activity) {
            $row = $DB->get_record('course_modules', array('id' => $activity));
            if (!empty($row) && $row->deletioninprogress == 0) {
                $newactivities = array();
                $newcm = $this->duplicate_activity($activity, $courseid, $newsection->id, $newactivities);
                $DB->update_record('course_modules', $row);

                $arrcmids[$activity] = $newcm[$activity];
                \local_metadata\mcontext::module()->save($newcm[$activity], 'ID', $activity);
            }
        }

        // Availability.
        foreach ($arrcmids as $oldcmid => $newcmid) {
            $rowold = $DB->get_record('course_modules', array('id' => $oldcmid));
            $rownew = $DB->get_record('course_modules', array('id' => $newcmid));

            if (!empty($rowold->availability) && $rownew) {
                $res = community_sharewith_change_availability(json_decode($rowold->availability), $newcmid, $arrcmids);

                if ($res != null) {
                    $rownew->availability = json_encode($res, JSON_NUMERIC_CHECK);
                } else {
                    $rownew->availability = null;
                }

                $DB->update_record('course_modules', $rownew);
            }
        }

        rebuild_course_cache($courseid);

        return isset($newsection) ? $newsection : null;
    }

    public function copy_mod_game($sourceactivityid, $targetactivity, $courseid, $sectionid) {
        global $DB;

        $sql = '
        SELECT m.name, cm.instance, cm.course
        FROM {course_modules} cm
        LEFT JOIN {modules} m ON(cm.module = m.id)
        WHERE cm.id = ?
    ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));

        if ($source->name == 'game') {

            if ($game = $DB->get_record('game', array('id' => $source->instance))) {

                if (in_array($game->sourcemodule, array('quiz', 'glossary'))) {
                    $cmactivity = $DB->get_record('course_modules', array('id' => $targetactivity->id));
                    $gameupdate = $DB->get_record('game', array('id' => $cmactivity->instance));
                }

                switch ($game->sourcemodule) {
                    case 'quiz':
                        $quiz = $DB->get_record('quiz', array('id' => $game->quizid));

                        if (!empty($quiz)) {
                            $cm = $DB->get_record('course_modules', array('course' => $quiz->course, 'instance' => $quiz->id));

                            $newactivities = array();
                            $activitysequence = false;

                            // PTL-1253.
                            //$availability = $this->get_activities_chain($cm->id, $cm->course);
                            //if (count($availability)) {
                            //    $activitysequence = array_column($availability, 'id');
                            //}
                            $newactivity =
                                    $this->duplicate_activity($cm->id, $courseid, $sectionid, $newactivities, $activitysequence);

                            $newcm = $DB->get_record('course_modules', array('id' => end($newactivity)));
                            $gameupdate->quizid = $newcm->instance;
                            $DB->update_record('game', $gameupdate);

                            // Copy availability.
                            // PTL-1253.
                            //$replace1 = array_keys($newactivity);
                            //$replace2 = array_values($newactivity);
                            //$newcm->availability = str_replace($replace1, $replace2, $cm->availability);
                            $newcm->availability = '';

                            $DB->update_record('course_modules', $newcm);

                            if ($this->copytype == 'banksharing') {
                                community_sharewith_update_meta_id($newactivity);
                            }

                            return $newactivity;
                        } else {
                            return null;
                        }
                        break;

                    case 'glossary':
                        $glossary = $DB->get_record('glossary', array('id' => $game->glossaryid));

                        if (!empty($glossary) && $glossary->course == $source->course) {
                            $cm = $DB->get_record('course_modules',
                                    array('course' => $glossary->course, 'instance' => $glossary->id));

                            $this->enable_glossary_copy_users();

                            $newactivities = array();
                            $activitysequence = false;

                            // PTL-1253.
                            //$availability = $this->get_activities_chain($cm->id, $cm->course);
                            //if (count($availability)) {
                            //    $activitysequence = array_column($availability, 'id');
                            //}

                            $newactivity =
                                    $this->duplicate_activity($cm->id, $courseid, $sectionid, $newactivities, $activitysequence);

                            $newcm = $DB->get_record('course_modules', array('id' => end($newactivity)));
                            $gameupdate->glossaryid = $newcm->instance;
                            $DB->update_record('game', $gameupdate);

                            // Copy availability.
                            // PTL-1253.
                            //$replace1 = array_keys($newactivity);
                            //$replace2 = array_values($newactivity);
                            //$newcm->availability = str_replace($replace1, $replace2, $cm->availability);
                            $newcm->availability = '';

                            $DB->update_record('course_modules', $newcm);

                            if ($this->copytype == 'banksharing') {
                                community_sharewith_update_meta_id(array('id' => end($newactivity)));
                            }

                            return $newactivity;
                        } else {
                            return null;
                        }
                        break;
                }
            }
        }

        return null;
    }

    public function get_types_content_metadata($shortname) {
        global $DB;

        $obj = $DB->get_record_sql("SELECT * FROM {local_metadata_field} WHERE contextlevel=? AND shortname=?",
                [$this->modulemetadata, $shortname]);

        $res = preg_split('/\R/', $obj->param1);
        $res = array_unique($res);

        $result = array();
        if (!empty($res)) {
            $countchecked = 0;
            foreach ($res as $str) {
                $arr = explode('|', $str);

                if (isset($arr[1]) && !empty($arr[1])) {
                    $icon = 'involve__button-image--' . $arr[1];
                } else {
                    $icon = '';
                }

                $checked = ($countchecked == 0) ? 'checked' : '';

                $result[] = array(
                        'metadata_name' => $arr[0],
                        'metadata_icon' => $icon,
                        'metadata_value' => $str,
                        'metadata_checked' => $checked
                );

                $countchecked++;
            }
        }

        return $result;
    }

    public function get_activities_chain($chain, $courseid) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        if (!is_array($chain)) {
            $tempmod = new Stdclass();
            $tempmod->id = $chain;
            $chain = array($tempmod);
        }

        $mod = end($chain);
        $cms = get_fast_modinfo($courseid);
        $cminfo = $cms->get_cm($mod->id);

        $mod->course = $cminfo->course;
        $mod->visible = $cminfo->visible;
        $mod->availability = $cminfo->availability;
        if (!empty($mod->availability)) {

            $availability = json_decode($mod->availability);

            if (isset($availability->c) && !empty($availability->c)) {
                foreach ($availability->c as $item) {
                    if (empty($cms->cms[$item->cm])) {
                        return $chain;
                    }
                    $chainmod = new Stdclass();
                    $chainmod->id = $item->cm;
                    $chain[] = $chainmod;

                    $chain = $this->get_activities_chain($chain, $courseid);
                }
            }
        }

        return $chain;
    }
}
