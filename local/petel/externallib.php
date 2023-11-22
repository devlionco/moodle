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
 * External functions.
 *
 * @package    local_petel
 * @copyright  2017 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/petel/locallib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

class local_petel_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function store_applet_data_parameters() {
        return new external_function_parameters(
                array('appletid' => new external_value(PARAM_ALPHANUM, 'appletid'),
                        'data' => new external_value(PARAM_RAW, 'data')
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return array of settings
     */
    public static function store_applet_data($appletid, $data) {
        global $DB;

        $params = self::validate_parameters(self::store_applet_data_parameters(),
                array('appletid' => $appletid, 'data' => $data));

        // Save data.
        $userdata = new \stdClass();
        $userdata->appletid = $appletid;
        $userdata->data = $data;
        $userdata->timecreated = time();
        $recordid = $DB->insert_record('applets_store', $userdata);

        return ['id' => 200, 'msg' => 'Ok'];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function store_applet_data_returns() {
        return new external_single_structure(
                array(
                        'id' => new external_value(PARAM_INT, 'ID error message'),
                        'msg' => new external_value(PARAM_TEXT, 'Text error message'),
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_courses_for_teachers_parameters() {
        return new external_function_parameters(
                array('categoryid' => new external_value(PARAM_INT, 'categoryid'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'roleid' => new external_value(PARAM_INT, 'roleid'),
                        'groups' => new external_value(PARAM_RAW, 'groups'),
                        'nullcheck' => new external_value(PARAM_BOOL, 'nullcheck'),
                        'currentuserid' => new external_value(PARAM_INT, 'currentuserid'),
                        'users' => new external_value(PARAM_RAW, 'users'),
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return array of settings
     */
    public static function create_courses_for_teachers($categoryid, $courseid, $roleid, $groups, $nullcheck, $currentuserid,
            $users) {
        global $DB;

        $params = self::validate_parameters(self::create_courses_for_teachers_parameters(),
                array(
                        'categoryid' => $categoryid,
                        'courseid' => $courseid,
                        'roleid' => $roleid,
                        'groups' => $groups,
                        'nullcheck' => $nullcheck,
                        'currentuserid' => $currentuserid,
                        'users' => $users,
                ));

        // Run ADHOC.
        $task = new \local_petel\task\adhoc_participiant();
        $task->set_custom_data(
                array(
                        'userids' => $users,
                        'categoryid' => $categoryid,
                        'courseid' => $courseid,
                        'roleid' => $roleid,
                        'groups' => $groups,
                        'nullcheck' => $nullcheck,
                        'currentuserid' => $currentuserid
                )
        );
        \core\task\manager::queue_adhoc_task($task);

        return ['result' => true];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function create_courses_for_teachers_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_system_groups_for_teachers_parameters() {
        return new external_function_parameters(
                array(
                        'groupids' => new external_value(PARAM_RAW, 'groupids'),
                        'currentuserid' => new external_value(PARAM_INT, 'currentuserid'),
                        'users' => new external_value(PARAM_RAW, 'users'),
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return array of settings
     */
    public static function create_system_groups_for_teachers($groupids, $currentuserid, $users) {
        global $DB;

        $params = self::validate_parameters(self::create_system_groups_for_teachers_parameters(),
                array(
                        'groupids' => $groupids,
                        'currentuserid' => $currentuserid,
                        'users' => $users,
                ));

        foreach (json_decode($params['users']) as $userid) {
            foreach (json_decode($params['groupids']) as $cohortid) {
                if (!$DB->get_record('cohort_members', ['cohortid' => $cohortid, 'userid' => $userid])) {
                    $DB->insert_record('cohort_members', [
                            'cohortid' => $cohortid,
                            'userid' => $userid,
                            'timeadded' => time()
                    ]);
                }
            }
        }

        return ['result' => true];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function create_system_groups_for_teachers_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_categories_ac_parameters() {
        return new external_function_parameters(
                array('query' => new external_value(PARAM_TEXT, 'query'))
        );
    }

    /**
     * Get categories
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function get_categories_ac($query = '') {
        global $DB;

        $categories = [];
        $query = (string) trim($query);

        if (empty($query)) {
            $sql = "SELECT cc.id, cc.name FROM {course_categories} cc LIMIT 10";
            $categoriesselect = $DB->get_records_sql($sql);
        }

        if (!empty($query)) {
            $query = $DB->sql_like_escape($query);

            $sql = "SELECT cc.id, cc.name FROM {course_categories} cc WHERE cc.name LIKE '%" . $query . "%' LIMIT 10";
            $categoriesselect = $DB->get_records_sql($sql);
        }

        foreach ($categoriesselect as $key => $cat) {
            $categories[$cat->id] = $cat->name;
        }

        return json_encode($categories);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_categories_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON categories');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_courses_ac_parameters() {
        return new external_function_parameters(
                array('query' => new external_value(PARAM_TEXT, 'query'))
        );
    }

    /**
     * Get categories
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function get_courses_ac($query = '') {
        global $CFG, $DB;

        $courses = [];
        $query = (string) trim($query);

        if (empty($query)) {
            $sql = "SELECT c.id, c.shortname FROM {course} c WHERE c.id > 1 AND c.visible = 1 LIMIT 10";
            $coursesselect = $DB->get_records_sql($sql);
        }

        if (!empty($query)) {
            $query = $DB->sql_like_escape($query);

            $sql = "SELECT c.id, c.shortname
                    FROM {course} c
                    WHERE c.id > 1 AND c.visible = 1 AND c.shortname LIKE '%" . $query . "%'
                    LIMIT 10
                    ";
            $coursesselect = $DB->get_records_sql($sql);
        }

        foreach ($coursesselect as $key => $c) {
            $courses[$c->id] = $c->shortname . ' (' . $c->id . ')';
        }

        return json_encode($courses);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_courses_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON courses');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_roles_ac_parameters() {
        return new external_function_parameters(
                array('query' => new external_value(PARAM_TEXT, 'query'))
        );
    }

    /**
     * Get roles
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function get_roles_ac($query = '') {
        global $DB;

        $roles = [];
        $query = (string) trim($query);

        // Get roles.
        $excluderoles = ['guest', 'user', 'frontpage'];
        foreach ($DB->get_records('role') as $role) {

            if (in_array($role->shortname, $excluderoles)) {
                continue;
            }

            if (!empty($role->name)) {
                $rolename = $role->name;
            } else {
                switch ($role->shortname) {
                    case 'manager':
                        $rolename = get_string('manager', 'role');
                        break;
                    case 'coursecreator':
                        $rolename = get_string('coursecreators');
                        break;
                    case 'editingteacher':
                        $rolename = get_string('defaultcourseteacher');
                        break;
                    case 'teacher':
                        $rolename = get_string('noneditingteacher');
                        break;
                    case 'student':
                        $rolename = get_string('defaultcoursestudent');
                        break;
                    case 'guest':
                        $rolename = get_string('guest');
                        break;
                    case 'user':
                        $rolename = get_string('authenticateduser');
                        break;
                    case 'frontpage':
                        $rolename = get_string('frontpageuser', 'role');
                        break;
                    default:
                        $rolename = $role->shortname;
                        break;
                }
            }

            $roles[$role->id] = $rolename;
        }

        if (!empty($query)) {
            foreach ($roles as $key => $rolename) {
                if (mb_strpos($rolename, $query) === false) {
                    unset($roles[$key]);
                }
            }
        }

        return json_encode($roles);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_roles_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON roles');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_system_groups_ac_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Get roles
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return string list of groups
     * @since Moodle 2.3
     */
    public static function get_system_groups_ac() {
        global $DB;

        $groups = [];

        foreach ($DB->get_records('cohort', ['visible' => 1]) as $cohort) {
            $groups[$cohort->id] = $cohort->name;
        }

        return json_encode($groups);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_system_groups_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON groups');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function check_user_idnumber_parameters() {
        return new external_function_parameters(
                array('currentuserid' => new external_value(PARAM_INT, 'userid'))
        );
    }

    /**
     * Check user idnumber
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function check_user_idnumber($currentuserid) {
        global $DB;

        $result = false;
        $user = $DB->get_record('user', array('id' => $currentuserid));

        if (isset($user->idnumber) && !empty($user->idnumber)) {
            $cat = $DB->get_record('course_categories', array('idnumber' => $user->idnumber));

            if (!empty($cat)) {
                $result = true;
            }
        }

        return ['result' => $result];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function check_user_idnumber_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );

    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function create_course_for_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'currentuserid' => new external_value(PARAM_INT, 'userid'),
                        'coursename' => new external_value(PARAM_RAW, 'coursename'),
                )
        );
    }

    /**
     * Create course for teacher
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function create_course_for_teacher($currentuserid, $coursename) {
        global $DB;

        if (empty(get_config('local_petel', 'default_course'))
                || empty(get_config('local_petel', 'admin_email')) || empty($currentuserid) || empty($coursename)
        ) {
            return ['result' => false];
        }

        $categoryid = 0;
        $user = $DB->get_record('user', array('id' => $currentuserid));
        if (isset($user->idnumber) && !empty($user->idnumber)) {
            $cat = $DB->get_record('course_categories', array('idnumber' => $user->idnumber));

            if (!empty($cat)) {
                $categoryid = $cat->id;
            } else {
                return ['result' => false];
            }
        } else {
            return ['result' => false];
        }

        // Run ADHOC.
        $task = new \local_petel\task\adhoc_createcourse();
        $task->set_custom_data(
                array(
                        'categoryid' => $categoryid,
                        'courseid' => get_config('local_petel', 'default_course'),
                        'currentuserid' => $currentuserid,
                        'coursename' => $coursename,
                )
        );
        \core\task\manager::queue_adhoc_task($task);

        return ['result' => true];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function create_course_for_teacher_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );

    }

    // Web services for Feinberg SIS
    // If a user does not exists, create user.
    // If a user exists, update it.
    // Enrol user to course with given role.

    public static function enrol_users_feinberg_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'enrollments' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'username' => new external_value(PARAM_RAW, 'username', VALUE_DEFAULT),
                                                'firstname' => new external_value(PARAM_TEXT, 'firstname', VALUE_DEFAULT),
                                                'lastname' => new external_value(PARAM_TEXT, 'lastname', VALUE_DEFAULT),
                                                'email' => new external_value(PARAM_EMAIL, 'email', VALUE_DEFAULT),
                                                'rolename' => new external_value(PARAM_ALPHA, 'rolename', VALUE_DEFAULT)
                                        )
                                )
                        )
                )
        );
    }

    /**
     * Enrolment of users.
     *
     * Function throw an exception at the first error encountered.
     *
     * @param integer $courseid An array of user enrolment
     * @param array $enrolments An array of user enrolment
     *              $enrolments is array of arrays containing user details
     * @throws dml_exception
     * @since Moodle 3.9
     */
    public static function enrol_users_feinberg($courseid, $enrollments) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/lib/moodlelib.php');

        $course = get_course($courseid);
        if (empty($course)) {
            throw new moodle_exception('cant find course', 'enrol_manual');
        }

        $params = self::validate_parameters(self::enrol_users_feinberg_parameters(),
                array('courseid' => $courseid, 'enrollments' => $enrollments));

        // Rollback all enrolment if an error occurs.
        $transaction = $DB->start_delegated_transaction();

        // Retrieve the manual enrolment plugin.
        $enroll = enrol_get_plugin('manual');
        if (empty($enroll)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        foreach ($params['enrollments'] as $enrollment) {

            // Update existing user.
            $existinguser = $DB->get_record('user', ['username' => $enrollment['username']]);
            if ($existinguser) { // Check if user exists.
                $user = new \stdClass();
                $user->id = $existinguser->id;
                $user->username = strtolower($enrollment['username']);
                $user->firstname = $enrollment['firstname'];
                $user->lastname = $enrollment['lastname'];
                $user->email = $enrollment['email'];
                user_update_user($user, false, false);
                $enrollment['userid'] = $user->id;

            } else {
                // Create user, if it does not exists.
                $newuser = new \stdClass();
                $newuser->username = strtolower($enrollment['username']);
                $newuser->firstname = $enrollment['firstname'];
                $newuser->lastname = $enrollment['lastname'];
                $newuser->email = $enrollment['email'];
                $newuser->lang = $CFG->enrol_users_feinberg_lang ?? 'en';
                $newuser->confirmed = 1;
                $newuser->policyagreed = 1;
                $newuser->mnethostid = $CFG->mnet_localhost_id;
                $newuser->password = generate_password();
                $newusertmp = user_create_user($newuser, false, false);
                $enrollment['userid'] = $newusertmp;

            }

            $roleid = $DB->get_record('role', ['shortname' => $enrollment['rolename']], 'id,shortname');
            $enrollment['roleid'] = $roleid->id;
            $enrollment['rolename'] = $roleid->shortname;
            // Ensure the current user is allowed to run this function in the enrolment context.
            $context = context_course::instance($courseid, IGNORE_MISSING);

            require_capability('enrol/manual:enrol', $context);

            // Throw an exception if user is not able to assign the role.
            $roles = get_assignable_roles($context);
            if (!array_key_exists($enrollment['roleid'], $roles)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrollment['roleid'];
                $errorparams->courseid = $courseid;
                $errorparams->userid = $enrollment['userid'];
                throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
            }

            // Check manual enrolment plugin instance is enabled/exist.
            $instance = null;
            $enrollinstances = enrol_get_instances($courseid, true);
            foreach ($enrollinstances as $courseenrolinstance) {
                if ($courseenrolinstance->enrol == "manual") {
                    $instance = $courseenrolinstance;
                    break;
                }
            }
            if (empty($instance)) {
                $errorparams = new stdClass();
                $errorparams->courseid = $courseid;
                throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
            }

            // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
            if (!$enroll->allow_enrol($instance)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrollment['roleid'];
                $errorparams->courseid = $courseid;
                $errorparams->userid = $enrollment['userid'];
                throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
            }

            // Finally proceed the enrolment.
            $enrollment['timestart'] = isset($enrollment['timestart']) ? $enrollment['timestart'] : time();
            $enrollment['timeend'] = isset($enrollment['timeend']) ? $enrollment['timeend'] : 0;
            $enrollment['status'] = (isset($enrollment['suspend']) && !empty($enrollment['suspend'])) ?
                    ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

            $enroll->enrol_user($instance, $enrollment['userid'], $enrollment['roleid'],
                    $enrollment['timestart'], $enrollment['timeend'], $enrollment['status']);

            // If role is editingteacher or assistantteacher enroll to cohort teachers.
            $supportroles = ($CFG->feinberg_support_roles) ? $CFG->feinberg_support_roles : 'editingteacher,assistantteacher';
            $supportrole = explode(',', $supportroles);
            if (in_array($enrollment['rolename'], $supportrole)) {
                $cohortid = $DB->get_record('cohort', ['idnumber' => $CFG->defaultcohortscourserequest], 'id');
                cohort_add_member($cohortid->id, $enrollment['userid']);
            }

        }
        $transaction->allow_commit();
        return ['result' => 'OK'];
    }

    /**
     * Returns description of method result value.
     *
     * @returncourseid
     * @since Moodle 2.2courseid
     */
    public static function enrol_users_feinberg_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_TEXT, 'text'),
                )
        );
    }

    public static function unenrol_users_feinberg_parameters() {
        return new external_function_parameters(array(
                'enrollments' => new external_multiple_structure(
                        new external_single_structure(
                                array(
                                        'username' => new external_value(PARAM_RAW, 'The user that is going to be unenrolled'),
                                        'courseid' => new external_value(PARAM_RAW, 'The course to unenrol the user from'),
                                        'rolename' => new external_value(PARAM_RAW, 'The user role name', VALUE_OPTIONAL)
                                )
                        )
                )
        ));
    }

    /**
     * Unenrolment of users.
     *
     * @param array $enrollments an array of course user and role ids
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function unenrol_users_feinberg($enrollments) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::unenrol_users_feinberg_parameters(), array('enrollments' => $enrollments));
        require_once($CFG->libdir . '/enrollib.php');
        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs.
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }
        $usersunenrolled = [];
        foreach ($params['enrollments'] as $enrollment) {
            $context = context_course::instance($enrollment['courseid']);
            require_capability('enrol/manual:unenrol', $context);

            $user = $DB->get_record('user', array('username' => strtolower($enrollment['username'])));
            if (!$user) {
                throw new moodle_exception('Username not exist: ' . $enrollment['username']);
            }

            if ($enrollment['rolename']) {
                $roleid = $DB->get_record('role', ['shortname' => $enrollment['rolename']], 'id');

                $isenrole = $DB->get_record('role_assignments',
                        array('contextid' => $context->id, 'userid' => $user->id, 'roleid' => $roleid->id));
                if (!$isenrole) {
                    throw new moodle_exception("The user:" . $user->username . " is not registered as " . $enrollment['rolename'] .
                            " in courseid = " . $enrollment['courseid']);
                }
                $assignments = $DB->get_records('role_assignments', array('contextid' => $context->id, 'userid' => $user->id));

                // Checking if enrolled the course in several rolles.
                if (count($assignments) > 1) {
                    // Deleting a specific role.
                    role_unassign_all(array('userid' => $user->id, 'contextid' => $context->id, 'roleid' => $roleid->id), false,
                            false);
                    $transaction->allow_commit();
                    return ['result' => 'OK'];
                }
            }

            $instance = $DB->get_record('enrol', array('courseid' => $enrollment['courseid'], 'enrol' => 'manual'));
            if (!$instance) {
                throw new moodle_exception("Manual enrolment doesn't exist or is disabled for role " . $enrollment['rolename'] .
                        " in courseid = " . $enrollment['courseid']);
            }

            if (!$enrol->allow_unenrol($instance)) {
                throw new moodle_exception('wscannotunenrol', 'enrol_manual', '', $enrollment);
            }
            $enrol->unenrol_user($instance, $user->id);
            $usersunenrolled[] = ['username' => $enrollment['username']];

            $supportroles = ($CFG->feinberg_support_roles) ? $CFG->feinberg_support_roles : 'editingteacher,assistantteacher';
            $supportrole = explode(',', $supportroles);
            if (in_array($enrollment['rolename'], $supportrole)) {
                $sql = "SELECT r.shortname
                        FROM {role_assignments} rs
                        JOIN {role} r ON r.id = rs.roleid
                        WHERE rs.userid = $user->id
                        GROUP BY r.shortname";

                // Get user other roles in courses.
                $userroles = $DB->get_records_sql($sql);
                $unenrollfromcohort = true;

                // If the user does not teach any course, his cohort registration deleted.
                foreach ($userroles as $role) {
                    if (in_array($role->shortname, $supportrole)) {
                        $unenrollfromcohort = false;
                    }
                }
                if ($unenrollfromcohort) {
                    $cohortid = $DB->get_record('cohort', ['idnumber' => $CFG->defaultcohortscourserequest], 'id');
                    cohort_remove_member($cohortid->id, $user->id);
                }
            }
        }
        $transaction->allow_commit();
        return ['result' => 'OK'];
    }

    /**
     * Returns description of method result value.
     *
     * @return null
     */
    public static function unenrol_users_feinberg_returns() {

        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_TEXT, 'text'),
                )
        );

    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function send_event_parameters() {
        return new external_function_parameters(
                array(
                        'type' => new external_value(PARAM_RAW, 'event type')
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return string of settings
     */
    public static function send_event($type) {
        global $USER;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::send_event_parameters(),
                array(
                        'type' => $type,
                )
        );

        switch ($params['type']) {
            case 'notification':
                $eventdata = [];
                $eventdata['context'] = $context;
                $eventdata['userid'] = $USER->id;
                $eventdata['other'] = ['type' => $params['type'], 'subject' => ''];
                $eventdata['objectid'] = $context->instanceid;

                \local_petel\event\notification_click::create($eventdata)->trigger();
                break;

            case 'chat':
                $eventdata = [];
                $eventdata['context'] = $context;
                $eventdata['userid'] = $USER->id;
                $eventdata['other'] = ['type' => $params['type'], 'subject' => ''];
                $eventdata['objectid'] = $context->instanceid;

                \local_petel\event\chat_click::create($eventdata)->trigger();
                break;
        }

        return '';
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function send_event_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }

}
