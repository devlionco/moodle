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
 * External interface library for customfields component
 *
 * @package   community_social
 * @copyright 2019 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/local/community/plugins/social/locallib.php');
require_once($CFG->dirroot . '/local/community/plugins/social/classes/event/social_followed.php');
require_once($CFG->dirroot . '/local/community/plugins/social/classes/event/approve_colleague.php');
require_once($CFG->dirroot . '/local/community/plugins/social/classes/event/decline_colleague.php');

require_login();

/**
 * Class community_social_external
 *
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_social_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function follow_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'follow_enable' => new external_value(PARAM_INT, 'follow_enable', VALUE_DEFAULT, null),
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_DEFAULT, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function follow_teacher_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Follow teacher
     *
     * @param int $followenable
     * @param int $pageuserid
     * @param int $currentuserid
     * @return array
     */
    public static function follow_teacher($followenable, $pageuserid, $currentuserid) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $isactive = 1;

        $obj = $DB->get_record('community_social_followers', array('userid' => $currentuserid, 'followuserid' => $pageuserid));
        if (!empty($obj)) {
            switch ($obj->isactive) {
                case 0:
                    $isactive = 1;
                    break;
                case 1:
                    $isactive = 0;
                    break;
                default:
                    $isactive = 0;
            }

            $obj->isactive = $isactive;
            $DB->update_record('community_social_followers', $obj);
        } else {
            $dataobject = new stdClass();
            $dataobject->userid = $currentuserid;
            $dataobject->followuserid = $pageuserid;
            $dataobject->isactive = $isactive;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('community_social_followers', $dataobject);

            // Send message to user.
            social_send_message_to_teacher($currentuserid, $pageuserid, '', 'community_social',
                    'social_folowers');
        }

        $userdetails = new \classUserDetails();
        $userdetails->set_user_id($currentuserid);
        $userdetails->get_followed(true);

        $userdetails = new \classUserDetails();
        $userdetails->set_user_id($pageuserid);
        $userdetails->get_followers(true);

        // Save Moodle Log.
        $eventdata['userid'] = $currentuserid;
        $eventdata['followuserid'] = $pageuserid;
        $eventdata['isactive'] = $isactive;
        \community_social\event\social_followed::create_event($USER->id, $eventdata)->trigger();

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_user_data_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_user_data_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @param int $userid
     * @return array
     */
    public static function render_block_user_data($userid) {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $data = array();
        $data['editable'] = social_if_editable_profile($userid);
        $data['if_followers'] = social_if_user_followers($userid);
        $data['if_colleagues'] = social_if_user_colleagues($userid);
        $data = array_merge($data, social_get_user_info_block($userid));

        $html = $OUTPUT->render_from_template('community_social/profile-aside-user-data', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_collegues_list_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_REQUIRED, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function user_collegues_list_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Request user collegues list
     *
     * @param int $pageuserid
     * @param int $currentuserid
     * @return array
     */
    public static function user_collegues_list($pageuserid, $currentuserid) {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $result = array();

        $collegues = social_get_colleagues($pageuserid);

        foreach ($collegues as $item) {
            $tmp = social_get_user_info($item->userid);
            $tmp->active_collegue = 1;
            $result['users'][] = $tmp;
        }

        $totalusers = count($result['users']);
        $userpage = social_get_user_info($pageuserid);

        $html = $OUTPUT->render_from_template('community_social/popup-request-collegues-list', array('data' => $result));

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('usercollegueslist', 'community_social') . ' ' . $userpage->firstname . ' ' .
                        $userpage->lastname . ' (' . $totalusers . ')'
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_follower_list_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_REQUIRED, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function user_follower_list_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Request user follower list
     *
     * @param int $pageuserid
     * @param int $currentuserid
     * @return array
     */
    public static function user_follower_list($pageuserid, $currentuserid) {
        global $DB, $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $result = array();
        $tmp = array();

        $followers = $DB->get_records('community_social_followers', array('followuserid' => $pageuserid, 'isactive' => 1));
        foreach ($followers as $item) {
            $tmp = social_get_user_info($item->userid);

            $tmp->active_follower = 0;
            if ($item->isactive) {
                $tmp->active_follower = 1;
            }

            $tmp->active_collegue = 0;
            if (social_if_user_colleagues($item->userid, $pageuserid)) {
                $tmp->active_collegue = 1;
            }

            $result['users'][] = $tmp;
        }

        $totalusers = count($result['users']);
        $userpage = social_get_user_info($pageuserid);

        $html = $OUTPUT->render_from_template('community_social/popup-request-follower-list', array('data' => $result));

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('userfollowerlist', 'community_social') . ' ' . $userpage->firstname . ' ' .
                        $userpage->lastname . ' (' . $totalusers . ')'
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_teacher_block_parameters() {
        return new external_function_parameters(
                array(
                        'teacher_tab' => new external_value(PARAM_INT, 'teacher_tab', VALUE_DEFAULT, null),
                        'search' => new external_value(PARAM_TEXT, 'search', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_teacher_block_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render teacher block after click on tab button
     *
     * @param int $teachertab
     * @param string $type
     * @return array
     */
    public static function render_teacher_block($teachertab, $search) {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $data = array();

        $data = array_merge($data, social_data_list_teachers($teachertab, $search));

        $html = $OUTPUT->render_from_template('community_social/teachers-card-block', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_public_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function popup_public_course_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Get courses for pombim
     *
     * @param int $userid
     * @return array
     */
    public static function popup_public_course($userid) {
        global $OUTPUT, $PAGE, $CFG, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $allcourses = social_get_users_courses($userid);
        $pombimcourses = social_get_courses_pombim($userid);
        $result = array();

        foreach ($allcourses as $key => $courseid) {
            $tmp = get_course($courseid);
            $tmp->checked = '';
            $tmp->counter = 'customid' . $key;
            $tmp->imageurl = get_course_image($courseid);
            $tmp->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $courseid;

            foreach ($pombimcourses['courses_pombim'] as $pombim) {
                if ($courseid == $pombim->id) {
                    $tmp->checked = 'checked';
                }
            }

            $result[] = $tmp;
        }

        $html = $OUTPUT->render_from_template('community_social/popup-public-course', array('data' => $result));

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('choosingpubliccourses', 'community_social')
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function save_selected_pombim_courses_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'ids' => new external_value(PARAM_TEXT, 'ids', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function save_selected_pombim_courses_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Save selected pombim courses
     *
     * @param int $userid
     * @param string $ids
     * @return array
     */
    public static function save_selected_pombim_courses($userid, $ids) {
        global $USER, $DB, $OUTPUT;

        $arrids = json_decode($ids);

        // Delete rows.
        $allrows = $DB->get_records('community_social_shrd_crss', array('userid' => $userid));
        foreach ($allrows as $item) {
            if (!in_array($item->courseid, $arrids)) {

                // Delete permission by course from all followed users.
                $row = $DB->get_record('community_social_shrd_crss', array('userid' => $userid, 'courseid' => $item->courseid));
                $collegues = $DB->get_records('community_social_collegues', array('social_shared_courses_id' => $row->id));
                foreach ($collegues as $colleg) {
                    $DB->update_record('community_social_collegues', array('id' => $colleg->id, 'approved' => 0));
                    social_close_permission_course($colleg->userid, $item->courseid);
                }

                $DB->delete_records('community_social_shrd_crss', array('userid' => $userid, 'courseid' => $item->courseid));
            }
        }

        foreach ($arrids as $courseid) {
            $row = $DB->get_record('community_social_shrd_crss', array('userid' => $userid, 'courseid' => $courseid));
            if (empty($row)) {
                $dataobject = new stdClass();
                $dataobject->userid = $userid;
                $dataobject->courseid = $courseid;
                $dataobject->timecreated = time();
                $dataobject->timemodified = time();
                $DB->insert_record('community_social_shrd_crss', $dataobject);
            }
        }

        \classUserDetails::activate_refresh($userid);

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_aside_courses_pombim_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_aside_courses_pombim_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render aside courses pombim
     *
     * @param int $userid
     * @return array
     */
    public static function render_block_aside_courses_pombim($userid) {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $data = array();
        $data['editable'] = social_if_editable_profile($userid);
        $data = array_merge($data, social_get_courses_pombim($userid));

        $html = $OUTPUT->render_from_template('community_social/profile-aside-public-course', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_courses_pombim_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_courses_pombim_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render courses pombim
     *
     * @param int $userid
     * @return array
     */
    public static function render_block_courses_pombim($userid) {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $data = array();
        $data['editable'] = social_if_editable_profile($userid);
        $data = array_merge($data, social_get_courses_pombim($userid));

        $html = $OUTPUT->render_from_template('community_social/profile-course', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function school_settings_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function school_settings_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * School settings
     *
     * @param int $userid
     * @return array
     */
    public static function school_settings($userid) {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $data = social_get_user_info_block($userid);

        $html = $OUTPUT->render_from_template('community_social/popup-school-settings', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('editingtheschool', 'community_social')
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function school_save_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'value' => new external_value(PARAM_TEXT, 'value', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function school_save_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * school_save
     *
     * @param int $userid
     * @param string $value
     * @return array
     */
    public static function school_save($userid, $value) {

        if (social_if_editable_profile($userid)) {
            social_update_school($userid, $value);

            \classUserDetails::activate_refresh($userid);
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function social_disable_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function social_disable_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * social_disable
     *
     * @return array
     */
    public static function social_disable() {
        global $OUTPUT, $PAGE, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $data = array(
                'social_disable' => new moodle_url('/local/community/plugins/social/index.php',
                        array_filter(array('id' => null, 'socialenable' => 0), 'social_filter_userid'))
        );

        $html = $OUTPUT->render_from_template('community_social/popup-social-disable', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('disablingsocialarea', 'community_social')
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function send_followed_courses_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_REQUIRED, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_REQUIRED, null),
                        'ids' => new external_value(PARAM_TEXT, 'ids', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function send_followed_courses_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Send followed courses
     *
     * @param int $pageuserid
     * @param int $currentuserid
     * @param string $ids
     * @return array
     */
    public static function send_followed_courses($pageuserid, $currentuserid, $ids) {
        global $USER, $DB, $CFG;

        $arrids = json_decode($ids);

        // Get social_shared_courses_ids by courseid.
        $sharedcoursesid = '';
        foreach ($arrids as $courseid) {
            $row = $DB->get_record('community_social_shrd_crss', array('userid' => $pageuserid, 'courseid' => $courseid));
            if (!empty($row)) {
                $sharedcoursesid = $row->id;
            }
        }

        // Send message to user with request.
        if (!empty($sharedcoursesid)) {

            // Send message to user.
            $messageid = social_send_message_to_teacher($USER->id, $pageuserid, $sharedcoursesid,
                    'community_social', 'social_request');

            // Delete old row.
            $res = $DB->get_record('community_social_requests', array('userid' => $pageuserid, 'usersendid' => $USER->id,
                    'social_shared_courses_ids' => $sharedcoursesid));
            if (!empty($res)) {
                $DB->delete_records('community_social_requests', array('userid' => $pageuserid, 'usersendid' => $USER->id,
                        'social_shared_courses_ids' => $sharedcoursesid));
            }

            $dataobject = new stdClass();
            $dataobject->userid = $pageuserid;
            $dataobject->usersendid = $USER->id;
            $dataobject->social_shared_courses_ids = $sharedcoursesid;
            $dataobject->status = 0;
            $dataobject->messageid = $messageid;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('community_social_requests', $dataobject);

            // Save Moodle Log.
            $eventdata['userid'] = $USER->id;
            $eventdata['targetuserid'] = $pageuserid;
            $eventdata['courses'] = $sharedcoursesid;
            \community_social\event\request_colleague::create_event($USER->id, $eventdata)->trigger();
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function remove_teacher_from_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function remove_teacher_from_course_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Remove teacher from course
     *
     * @param int userid
     * @param int courseid
     * @return array
     */
    public static function remove_teacher_from_course($userid, $courseid) {
        global $DB;

        $obj = $DB->get_record('community_social_collegues', array('userid' => $userid, 'social_shared_courses_id' => $courseid));
        if (!empty($obj)) {
            $obj->approved = 0;
            $DB->update_record('community_social_collegues', $obj);

            $row = $DB->get_record('community_social_shrd_crss', array('id' => $courseid));
            if (!empty($row)) {
                social_close_permission_course($userid, $row->courseid);
            }

            \classUserDetails::activate_refresh($userid);
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function request_followed_courses_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_DEFAULT, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function request_followed_courses_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Request followed courses
     *
     * @param int page_userid
     * @param int current_userid
     * @return array
     */
    public static function request_followed_courses($pageuserid, $currentuserid) {
        global $CFG, $OUTPUT;

        $pombimcourses = social_get_courses_pombim($pageuserid);
        $result = array();

        foreach ($pombimcourses['courses_pombim'] as $key => $course) {
            $tmp = get_course($course->id);
            $tmp->checked = '';
            $tmp->counter = 'customid' . $key;
            $tmp->imageurl = get_course_image($course->id);
            $tmp->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;

            /*foreach($pombimcourses['courses_pombim'] as $pombim){
                if($course->id == $pombim->id){
                    $tmp->checked = 'checked';
                }
            }*/

            $result[] = $tmp;
        }

        $html = $OUTPUT->render_from_template('community_social/popup-request-followed-courses', array('data' => $result));

        $arrcontent = array(
                'content' => $html,
                'header' => 'select followed courses'
        );
        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function change_follow_teacher_by_user_parameters() {
        return new external_function_parameters(
                array(
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_DEFAULT, null),
                        'custom_userid' => new external_value(PARAM_INT, 'custom_userid', VALUE_DEFAULT, null),
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function change_follow_teacher_by_user_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Change follow teacher by user
     *
     * @param int page_userid
     * @param int current_userid
     * @param int custom_userid
     * @return array
     */
    public static function change_follow_teacher_by_user($currentuserid, $customuserid, $pageuserid) {
        global $USER, $DB;

        $obj = $DB->get_record('community_social_followers', array('userid' => $customuserid, 'followuserid' => $pageuserid));
        if (!empty($obj)) {
            $obj->isactive = 0;
            $DB->update_record('community_social_followers', $obj);
        }

        // Send message.
        social_send_message_to_teacher($customuserid, $pageuserid, '', '', 'community_social', 'social_folowers');

        $userdetails = new \classUserDetails();
        $userdetails->set_user_id($customuserid);
        $userdetails->get_followed(true);

        $userdetails = new \classUserDetails();
        $userdetails->set_user_id($pageuserid);
        $userdetails->get_followers(true);

        // Save Moodle Log.
        $eventdata['userid'] = $customuserid;
        $eventdata['followuserid'] = $pageuserid;
        $eventdata['isactive'] = 0;
        \community_social\event\social_followed::create_event($USER->id, $eventdata)->trigger();

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_subjects_oercatalog_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_subjects_oercatalog_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render block subjects oercatalog
     *
     * @param int $userid
     * @return array
     */
    public static function render_block_subjects_oercatalog($userid) {
        global $OUTPUT;

        // Recache user.
        $userdetails = new \classUserDetails();
        $userdetails->set_user_id($userid);
        $userdetails->update_teacher();

        $data = array();
        $data['editable'] = social_if_editable_profile($userid);
        $data = array_merge($data, social_get_user_info_block($userid));

        $html = $OUTPUT->render_from_template('community_social/profile-subjects', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );
        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function approve_message_from_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'messageid' => new external_value(PARAM_INT, 'message id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function approve_message_from_teacher_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Approve message from teacher
     *
     * @param int $messageid
     * @return array
     */
    public static function approve_message_from_teacher($messageid) {
        global $USER, $DB, $CFG;

        $obj = $DB->get_record('community_social_requests', array('messageid' => $messageid, 'userid' => $USER->id));
        if (!empty($obj)) {
            social_approve_courses_to_user($obj->usersendid, $obj->social_shared_courses_ids);

            $obj->status = 1;
            $DB->update_record('community_social_requests', $obj);

            // Send message to user with result.
            $row = $DB->get_record('notifications', array('id' => $messageid));
            if (!empty($row)) {
                social_send_message_to_teacher($USER->id, $row->useridfrom, $obj->social_shared_courses_ids,
                        'community_social', 'social_approve');
            }

            // Change eventtype to notification.
            $rowobj = $DB->get_record('notifications', array('id' => $messageid));
            if (!empty($rowobj)) {
                $rowobj->eventtype = 'social_approve_complete';
                $rowobj->timeread = time();

                $tmp = json_decode($rowobj->customdata);
                $tmp->social_request = false;
                $tmp->social_approve_complete = true;
                $tmp->content = get_string('requestapprovecompletetocourse', 'community_social');
                $rowobj->customdata = json_encode($tmp);

                $DB->update_record('notifications', $rowobj);
            }

            // Send mail to user.
            $userto = $DB->get_record('user', array('id' => $obj->usersendid));
            $userfrom = $CFG->noreplyaddress;
            $subject = get_string('infomessageforteacher', 'community_social');

            $requestuser = $DB->get_record('user', array('id' => $obj->userid));

            $a = new stdClass();
            $a->userName = $requestuser->firstname . ' ' . $requestuser->lastname;
            $a->courseNames = '';

            $sharedcoursesids = explode(',', $obj->social_shared_courses_ids);
            foreach ($sharedcoursesids as $courseid) {
                $row = $DB->get_record('community_social_shrd_crss', array('id' => $courseid));
                $course = get_course($row->courseid);
                $a->courseNames .= $course->fullname . ' ';
                $a->courseUrl .= $CFG->wwwroot . '/course/view.php?id=' . $course->id;
            }

            $bodyhtml = get_string('coursepombimapproveforuser', 'community_social', $a);
            email_to_user($userto, $userfrom, $subject, '', $bodyhtml);

            // Save Moodle Log.
            $eventdata['userid'] = $obj->userid;
            $eventdata['targetuserid'] = $obj->usersendid;
            $eventdata['courses'] = $obj->social_shared_courses_ids;
            \community_social\event\approve_colleague::create_event($USER->id, $eventdata)->trigger();

            \classUserDetails::activate_refresh($obj->userid);
            \classUserDetails::activate_refresh($obj->usersendid);
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function decline_message_from_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'messageid' => new external_value(PARAM_INT, 'message id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function decline_message_from_teacher_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Decline message from teacher
     *
     * @param int $messageid
     * @return array
     */
    public static function decline_message_from_teacher($messageid) {
        global $USER, $DB, $CFG;

        $obj = $DB->get_record('community_social_requests', array('messageid' => $messageid, 'userid' => $USER->id));
        if (!empty($obj)) {
            $obj->status = 2;
            $DB->update_record('community_social_requests', $obj);

            // Send message to user with result.
            $row = $DB->get_record('notifications', array('id' => $messageid));
            if (!empty($row)) {
                social_send_message_to_teacher($USER->id, $row->useridfrom, $obj->social_shared_courses_ids,
                        'community_social', 'social_decline');
            }

            // Change eventtype to notification.
            $rowobj = $DB->get_record('notifications', array('id' => $messageid));
            if (!empty($rowobj)) {
                $rowobj->eventtype = 'social_decline_complete';

                $tmp = json_decode($rowobj->customdata);
                $tmp->social_request = false;
                $tmp->social_decline_complete = true;
                $tmp->content = get_string('requestdeclinecompletetocourse', 'community_social');
                $rowobj->customdata = json_encode($tmp);

                $rowobj->timeread = time();
                $DB->update_record('notifications', $rowobj);
            }

            // Send mail to user.
            $userto = $DB->get_record('user', array('id' => $obj->usersendid));
            $userfrom = $CFG->noreplyaddress;
            $subject = get_string('infomessageforteacher', 'community_social');

            $requestuser = $DB->get_record('user', array('id' => $obj->userid));

            $a = new stdClass();
            $a->userName = $requestuser->firstname . ' ' . $requestuser->lastname;
            $a->courseNames = '';

            $sharedcoursesids = explode(',', $obj->social_shared_courses_ids);
            foreach ($sharedcoursesids as $courseid) {
                $row = $DB->get_record('community_social_shrd_crss', array('id' => $courseid));
                $course = get_course($row->courseid);
                $a->courseNames .= $course->fullname . ' ';
            }

            $bodyhtml = get_string('coursepombimdeclineforuser', 'community_social', $a);
            email_to_user($userto, $userfrom, $subject, '', $bodyhtml);

            // Save Moodle Log.
            $eventdata['userid'] = $obj->userid;
            $eventdata['targetuserid'] = $obj->usersendid;
            \community_social\event\decline_colleague::create_event($USER->id, $eventdata)->trigger();
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function remove_teacher_request_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course id', VALUE_DEFAULT, null),
                        'userid' => new external_value(PARAM_INT, 'user id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function remove_teacher_request_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Remove teacher request
     *
     * @param int $courseid
     * @param int $userid
     * @return array
     */
    public static function remove_teacher_request($courseid, $userid) {
        global $DB, $OUTPUT;

        $result = array();

        $sharedobj = $DB->get_record("community_social_shrd_crss", array('id' => $courseid));
        $course = get_course($sharedobj->courseid);
        $user = social_get_user_info($userid);

        $result['courseid'] = $courseid;
        $result['userid'] = $userid;
        $result['course_name'] = $course->fullname;
        $result['user_firstname'] = $user->firstname;
        $result['user_lastname'] = $user->lastname;

        $html = $OUTPUT->render_from_template('community_social/popup-request-remove-teacher', array('data' => $result));

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('removepeerteacher', 'community_social')
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_migrate_public_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function popup_migrate_public_course_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Get courses for pombim
     *
     * @param int $userid
     * @return array
     */
    public static function popup_migrate_public_course($userid) {
        global $OUTPUT, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $allcourses = social_get_users_courses($userid);
        $pombimcourses = social_get_courses_pombim($userid);

        // Prepare courses pombim.
        $pombim = [];
        $exclude = [];
        foreach ($pombimcourses['courses_pombim'] as $item) {
            $pombim[] = ['id' => $item->id, 'name' => $item->fullname];
            $exclude[] = $item->id;
        }

        // Prepare courses for migrate.
        $migrate = [];
        foreach ($allcourses as $id) {

            if (in_array($id, $exclude)) {
                continue;
            }

            $course = get_course($id);
            $migrate[] = ['id' => $course->id, 'name' => $course->fullname];
        }

        $html = $OUTPUT->render_from_template('community_social/migrate-public-course', array(
                'pombim' => $pombim,
                'migrate' => $migrate,
                'migrate_enable' => count($pombim) && count($migrate),
        ));

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('migratepubliccourses', 'community_social')
        );

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function migrate_public_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'oldcourseid' => new external_value(PARAM_INT, 'public course id', VALUE_DEFAULT, null),
                        'newcourseid' => new external_value(PARAM_INT, 'my course id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function migrate_public_course_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Get courses for pombim
     *
     * @param int $userid
     * @return array
     */
    public static function migrate_public_course($userid, $oldcourseid, $newcourseid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        social_migrate_course($oldcourseid, $newcourseid);

        return array();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_are_you_sure_parameters() {
        return new external_function_parameters(
                array(
                        'data' => new external_value(PARAM_RAW, 'data', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function popup_are_you_sure_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Get popup are you shure
     *
     * @param int $userid
     * @return array
     */
    public static function popup_are_you_sure($data) {
        global $OUTPUT, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $courses = json_decode($data);
        $o = '';
        foreach ($courses as $key => $obj) {
            if ($obj->name === 'oldcourseid' || $obj->name === 'newcourseid') {
                $courseId = $obj->value;
                $course =  get_course($courseId); // Using Moodle's function to get course details.

                if ($obj->name === 'oldcourseid') {
                    // Add a new property 'courseName' with the course's full name.
                    $o .= "<strong>" . get_string('oldcourseverify', 'community_social') . "</strong>" . " " . $course->fullname . "<br>";
                }
                else{
                    $o .= "<strong>" . get_string('newcourseverify', 'community_social') . "</strong>" . " " . $course->fullname . "?" . "<br>";
                }
            }
        }

        $html = $OUTPUT->render_from_template('community_social/are-you-shure', array('data' => $data, 'courses' => $o));

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('titleareyoushure', 'community_social')
        );

        return $arrcontent;
    }
}
