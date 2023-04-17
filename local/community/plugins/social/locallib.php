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
 * Plugin general functions are defined here.
 *
 * @package     community_social
 * @category    admin
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/community/plugins/social/classes/classUserDetails.php');
require_once($CFG->dirroot . '/local/community/plugins/social/classes/event/request_colleague.php');
require_once($CFG->dirroot . '/local/community/locallib.php');

function social_filter_userid($var) {
    return ($var !== null && $var !== false && $var !== '');
}

function social_has_permission($userid) {
    global $DB, $CFG, $USER;

    if (!empty($CFG->defaultcohortscourserequest)) {
        $permitedcohorts = explode(',', $CFG->defaultcohortscourserequest);
        if ($permitedcohorts) {
            require_once($CFG->dirroot . '/cohort/lib.php');
            $cohorts = cohort_get_user_cohorts($USER->id);
            foreach ($cohorts as $cohort) {
                if (in_array($cohort->idnumber, $permitedcohorts)) {
                    return true;
                }
            }
        }
    }

    if (local_community_get_instancename() === 'physics') {
        $rolespermitted = array('manager', 'coursecreator', 'editingteacher', 'teacher');
    } else {
        // Hide from chemistry & biology.
        $rolespermitted = array('manager', 'coursecreator', 'editingteacher');
    }

    $sql = "SELECT DISTINCT (shortname) FROM {role} 
                LEFT JOIN {role_assignments} ON ({role}.id={role_assignments}.roleid) WHERE userid=?";

    $roles = $DB->get_records_sql($sql, array($userid));

    if (!empty($roles)) {
        foreach ($roles as $role) {
            if (in_array($role->shortname, $rolespermitted)) {
                return true;
            }
        }
    }

    return false;
}

function social_if_user_enable_social($userid) {
    return get_user_preferences('community_social_enable', '', $userid);
}

function social_relevant_userid($userid) {
    global $USER;
    if ($userid == null || $userid == 0) {
        $userid = $USER->id;
    }
    return $userid;
}

function social_if_wrong_user($userid) {
    global $DB;
    if ($userid) {
        $userobj = $DB->get_record("user", array('id' => $userid));
        if (!empty($userobj)) {
            return false;
        }
    }
    return true;
}

function social_prepare_profile_user_link($pageuserid) {
    global $USER, $CFG;

    // Check if admin.
    $admins = get_admins();
    $isadmin = false;

    foreach ($admins as $admin) {
        if ($USER->id == $admin->id) {
            $isadmin = true;
            break;
        }
    }

    if ($isadmin) {
        return $CFG->wwwroot . '/user/profile.php?id=' . $pageuserid;
    }

    return false;
}

function social_if_editable_profile($userid) {
    global $USER;

    if ($userid && $userid == $USER->id) {
        return true;
    }
    return false;
}

// Get course image url.
function get_course_image($courseid) {
    global $CFG, $OUTPUT;

    $imgurl = '';

    $course = get_course($courseid);
    if ($course instanceof \stdClass) {
        $course = new \core_course_list_element($course);
    }

    $noimgurl = $OUTPUT->image_url('noimg', 'theme');

    foreach ($course->get_course_overviewfiles() as $file) {
        $isimage = $file->is_valid_image();
        $imgurl = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);

        if (!$isimage) {
            $imgurl = $noimgurl;
        }
    }

    return $imgurl;
}

// Get course collegues.
function get_course_collegues($userid, $courseid) {
    global $DB, $USER;

    $result = array();

    $sql = "
        SELECT  lsc.id, lssc.courseid,
            lsc.social_shared_courses_id,
            lsc.userid,
            u.firstname,
            u.lastname
        FROM {community_social_shrd_crss} lssc
        LEFT JOIN {community_social_collegues} lsc ON(lssc.id=lsc.social_shared_courses_id)
        LEFT JOIN {user} u ON(lsc.userid=u.id)
        WHERE lssc.userid=? AND lssc.courseid=? AND lsc.approved=1
    ";
    $collegues = $DB->get_records_sql($sql, array($userid, $courseid));
    $collegues = array_values($collegues);

    // Add flag if can see url to course.
    $flagseeurlincourse = (social_if_editable_profile($userid)) ? true : false;

    // Add flag if can delete.
    foreach ($collegues as $item) {
        if (social_if_editable_profile($userid)) {
            $item->if_can_delete = true;
            $flagseeurlincourse = true;
        } else {
            if ($item->userid == $USER->id) {
                $item->if_can_delete = true;
                $flagseeurlincourse = true;
            } else {
                $item->if_can_delete = false;
            }
        }
    }
    if (is_siteadmin()) {
        $flagseeurlincourse = true;
    }

    // PTL-8544.
    $flagseeurlincourse = true;

    $result['collegues'] = $collegues;
    $result['collegues_counter'] = count($collegues);
    $result['if_can_see_url'] = $flagseeurlincourse;

    return $result;
}

function social_get_user_info($userid) {
    global $DB, $CFG;

    if ($userid) {
        $userobj = $DB->get_record("user", array('id' => $userid));
        if (!empty($userobj)) {
            $userobj->image_url = $CFG->wwwroot . '/user/pix.php/' . $userid . '/f1.jpg';

            // Get school.
            $school = $DB->get_record('user_info_field', array('shortname' => 'school'));
            if (!empty($school)) {
                $res = $DB->get_record('user_info_data', array('fieldid' => $school->id, 'userid' => $userid));

                $userobj->school = '';
                if (!empty($res)) {
                    $userobj->school = $res->data;
                }
            }

            // Get url profile.
            $userobj->url_user_profile = $CFG->wwwroot . '/local/community/plugins/social/profile.php?id=' . $userid;

            // Link to massage.
            $userobj->url_to_message = $CFG->wwwroot . '/message/index.php?id=' . $userid;

            return $userobj;
        }
    }
    return array();
}

// Colleagues .עמיתים.
function social_get_colleagues($userid) {
    global $DB;

    $sql = "SELECT lsc.userid
            FROM {community_social_shrd_crss} lssc
            LEFT JOIN {community_social_collegues} lsc ON(lssc.id=lsc.social_shared_courses_id)
            WHERE lssc.userid=? AND lsc.approved=1
            GROUP BY lsc.userid";

    $result = $DB->get_records_sql($sql, array($userid));
    return $result;
}

// Colleagues עמיתים count.
function social_get_colleagues_count($userid) {
    return count(social_get_colleagues($userid));
}

// Followers עוקבים.
function social_get_followers($userid) {
    global $DB;

    return $DB->get_records('community_social_followers', array('followuserid' => $userid, 'isactive' => 1));
}

// Followers עוקבים count.
function social_get_followers_count($userid) {
    global $DB;

    return count(social_get_followers($userid));
}

// Followed נעקבים.
function social_get_followed_count($userid) {
    global $DB;

    return $DB->count_records('community_social_followers', array('userid' => $userid, 'isactive' => 1));
}

// Count shared courses.
function social_get_shared_courses_count($userid) {
    global $DB;

    return $DB->count_records('community_social_shrd_crss', array('userid' => $userid));
}

// Get user's lastaccess.
function social_get_lastaccess($userid) {
    global $DB;

    // Hack .
    return time();

    // PTL-2773 Improve performance of SQL WHERE.
    $sql = "
        SELECT * 
        FROM {logstore_standard_log}
        WHERE eventname = '\\community_social\\event\\social_view' AND userid = ?
        ORDER BY id DESC        
        ";

    $obj = $DB->get_records_sql($sql, array($userid));
    $obj = array_values($obj);

    if (!empty($obj)) {
        return $obj[0]->timecreated;
    }

    return '';
}

// If user (Followers) עוקבים.
function social_if_user_followers($userid, $foruser = null) {
    global $DB, $USER;

    if ($foruser == null) {
        $foruser = $USER->id;
    }

    if ($userid != $foruser) {
        return $DB->count_records('community_social_followers',
                array('userid' => $foruser, 'followuserid' => $userid, 'isactive' => 1));
    }
    return false;
}

// If user (Followed) נעקבים.
function social_if_user_followed($userid, $foruser = null) {
    global $DB, $USER;

    if ($foruser == null) {
        $foruser = $USER->id;
    }

    if ($userid != $foruser) {
        return $DB->count_records('community_social_followers',
                array('userid' => $userid, 'followuserid' => $foruser, 'isactive' => 1));
    }
    return false;
}

// If user (colleagues) עמיתים.
function social_if_user_colleagues($userid, $foruser = null) {
    global $DB, $USER;

    if ($foruser == null) {
        $foruser = $USER->id;
    }

    if ($userid != $foruser) {
        $sql = "SELECT lsc.userid
            FROM {community_social_shrd_crss} lssc
            LEFT JOIN {community_social_collegues} lsc ON(lssc.id=lsc.social_shared_courses_id)
            WHERE lssc.userid=? AND lsc.userid=? AND lsc.approved=1
            GROUP BY lsc.userid";

        $tmp = $DB->get_records_sql($sql, array($foruser, $userid));

        if (count($tmp) > 0) {
            return true;
        }
    }
    return false;
}

// If_you_colleague_for_user (colleagues) עמיתים.
function social_if_you_colleague_for_user($userid, $foruser = null) {
    global $DB, $USER;

    if ($foruser == null) {
        $foruser = $USER->id;
    }

    if ($userid != $foruser) {
        $sql = "SELECT lsc.userid
            FROM {community_social_shrd_crss} lssc
            LEFT JOIN {community_social_collegues} lsc ON(lssc.id=lsc.social_shared_courses_id)
            WHERE lssc.userid=? AND lsc.userid=? AND lsc.approved=1
            GROUP BY lsc.userid";

        $tmp = $DB->get_records_sql($sql, array($userid, $foruser));

        if (count($tmp) > 0) {
            return true;
        }
    }
    return false;
}

function social_get_user_info_block($userid) {
    global $DB, $CFG;

    $data = array();
    $data['user'] = social_get_user_info($userid);

    $userdetails = new classUserDetails();
    $userdetails->set_user_id($userid);

    $data['firstname_oercatalog'] = $data['user']->firstname;
    $data['colleagues_count'] = $userdetails->get_colleagues();
    $data['folowers_count'] = $userdetails->get_followers();
    $data['followed_count'] = $userdetails->get_followed();
    $data['count_shared_courses'] = $userdetails->get_shared_courses_in_social();
    $data['last_access'] = $userdetails->get_last_access();
    $data['count_oercatalog_activities'] = $userdetails->get_count_oer_activities();
    $data['users_used_my_oercatalog_count'] = $userdetails->get_count_used_oer_activities();
    $data = array_merge($data, $userdetails->get_oercatalog_activities());

    // Get user Badges.
    require_once($CFG->dirroot . '/badges/renderer.php');

    if ($badgeissued = $DB->get_records('badge_issued', ['userid' => $userid])) {
        foreach ($badgeissued as $badge) {
            $userbadge = new \core_badges\output\issued_badge($badge->uniquehash);
            $data['badges'] = ['badge' => [
                    'image' => $userbadge->badgeclass['image'],
                    'name' => $userbadge->badgeclass['name'],
                    'url' => $CFG->wwwroot . '/badges/badge.php?hash=' . $badge->uniquehash
            ]
            ];
        }
    }

    return $data;
}

// Get all courses by user.
function social_get_users_courses($userid) {
    $result = array();
    if ($userid) {

        list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();

        $courses = enrol_get_users_courses($userid, true);
        if (!empty($courses)) {
            foreach ($courses as $course) {

                $context = context_course::instance($course->id);
                $roles = get_user_roles($context, $userid);

                $flagpermission = true;
                foreach ($roles as $role) {
                    if ($role->shortname == 'teachercolleague') {
                        $flagpermission = false;
                    }
                }

                if (!in_array($course->id, $oercourses) && $flagpermission) {
                    $result[] = $course->id;
                }
            }
        }
    }
    return $result;
}

// Get status of request.
function social_get_status_request($userid, $courseid) {
    global $DB, $USER;

    $sharedcourse = $DB->get_record('community_social_shrd_crss', array('userid' => $userid, 'courseid' => $courseid));
    if (!empty($sharedcourse)) {

        $sql = "
            SELECT *
            FROM (SELECT *
            FROM {community_social_requests}
            ORDER BY id DESC) AS fg
            WHERE usersendid=? AND userid=? AND social_shared_courses_ids=?
            GROUP BY social_shared_courses_ids
        ";

        $request = $DB->get_record_sql($sql, array($USER->id, $userid, $sharedcourse->id));
        if (!empty($request)) {

            if ($request->status == 1) {
                $collegue = $DB->get_record('community_social_collegues',
                        array('userid' => $USER->id, 'social_shared_courses_id' => $sharedcourse->id));
                if ($collegue->approved == 0) {
                    return 'empty';
                }
            }

            return $request->status;
        } else {
            return 'empty';
        }
    }

    return 'error';
}

// Get courses pombim.
function social_get_courses_pombim($userid) {
    global $DB, $CFG, $USER;
    $rescourses = array();

    if ($userid == 0) {
        // Get all public courses.
        $courses = $DB->get_records('community_social_shrd_crss');
        // Switch to current viewing user.
        $userid = $USER->id;
    } else {
        $courses = $DB->get_records('community_social_shrd_crss', array('userid' => $userid));
    }

    $countcollegues = 0;
    foreach ($courses as $item) {

        $collegues = get_course_collegues($userid, $item->courseid);
        $countcollegues += $collegues['collegues_counter'];

        try {
            $tmp = get_course($item->courseid);
        } catch (moodle_exception $e) {
            continue;
        }

        $coursesections = $DB->get_records('course_sections', ['course' => $item->courseid]);
        $sectionnames = array();
        foreach ($coursesections as $section) {
            if (!empty($section->name)) {
                $sectionnames[] = $section->name . " || ";
            }
        }
        $tmp->sectionlist = strip_tags(html_writer::alist($sectionnames));

        $tmp->summary = strip_tags($tmp->summary);

        $tmp->imageurl = get_course_image($item->courseid) ? get_course_image($item->courseid) :
                $CFG->wwwroot . '/local/community/plugins/social/pix/defaultbg.jpg';
        $tmp->collegues = $collegues['collegues'];
        $tmp->if_can_see_url = $collegues['if_can_see_url'];

        if ($userid == $USER->id) {
            $tmp->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $item->courseid;
        } else {
            $url = new moodle_url('/local/community/plugins/social/enrol_course_collegues.php', [
                    'id' => $item->courseid,
                    'userid' => $userid
            ]);
            $tmp->courseurl = $url->out();
        }

        $status = social_get_status_request($userid, $item->courseid);

        switch ($status) {
            case '0':
                $tmp->button_send_request = false;
                $tmp->button_wait_for_answer = true;
                $tmp->button_request_decline = false;
                break;
            case '2':
                $tmp->button_send_request = false;
                $tmp->button_wait_for_answer = false;
                $tmp->button_request_decline = true;
                break;
            case 'empty':
                $tmp->button_send_request = true;
                $tmp->button_wait_for_answer = false;
                $tmp->button_request_decline = false;
                break;
            default:
                $tmp->button_send_request = false;
                $tmp->button_wait_for_answer = false;
                $tmp->button_request_decline = false;
        }

        // PTL-8544.
        $tmp->button_send_request = false;

        $rescourses[] = $tmp;
    }

    $user = social_get_user_info($userid);

    if (!social_if_editable_profile($userid) && !empty($rescourses)) {
        $buttoncoursespombimenable = true;
    } else {
        $buttoncoursespombimenable = false;
    }

    $result = array(
            'courses_pombim' => $rescourses,
            'count_courses_pombim' => count($rescourses),
            'count_collegues' => $countcollegues,
            'firstname_pombim' => $user->firstname,
            'button_courses_pombim_enable' => $buttoncoursespombimenable,
    );

    return $result;
}

// Get own user's courses, where user get approved access as collegue.
function social_get_user_shared_courses($userid) {
    global $DB, $USER, $CFG;
    $courses = array();

    $sql = "
    SELECT lssc.courseid
    FROM {community_social_shrd_crss} lssc
    LEFT JOIN {community_social_collegues} lsc ON (lssc.id = lsc.social_shared_courses_id)
    INNER JOIN {course} cc ON (cc.id=lssc.courseid)
    WHERE lsc.userid = ? AND lssc.userid = ? AND lsc.approved = 1
    ";
    $sharedcourses = $DB->get_records_sql($sql, array($USER->id, $userid));

    foreach ($sharedcourses as $id => $c) {
        $courses[$id] = get_course($id);
        $courses[$id]->courseimage =
                get_course_image($id) ? get_course_image($id) : $CFG->wwwroot . '/local/community/plugins/social/pix/defaultbg.jpg';
    }

    $courses = array_values($courses);

    return json_encode($courses);
}

// Get activities for learn stat.
function social_get_courses_learn_stat($userid) {
    global $DB, $CFG;

    // Get activities by user.
    $sql = "
        SELECT ol.activityid, ol.courseid, cm.module, m.name AS mod_type, cm.instance, ol.timemodified, cm.section, cs.name AS section_name, cs.section AS num_section
        FROM {community_oercatalog_log} ol
        LEFT JOIN {course_modules} cm ON(ol.activityid=cm.id)
        LEFT JOIN {modules} m ON(cm.module=m.id)
        LEFT JOIN {course_sections} cs ON(cm.section=cs.id)
        WHERE ol.userid=?
        GROUP BY cm.section
        ORDER BY ol.timemodified DESC
        LIMIT 5
    ";
    $activities = $DB->get_records_sql($sql, array($userid));

    foreach ($activities as $item) {
        $urltoactivity = '';
        if (!empty($item->mod_type)) {
            $sql = "
            SELECT *
            FROM {" . $item->mod_type . "}
            WHERE id=?";
            $activity = $DB->get_record_sql($sql, [$item->instance]);
            $item->activity_name = $activity->name;

            // Prepare url to activity.
            switch ($item->mod_type) {
                case "quiz":
                    $urltoactivity = $CFG->wwwroot . '/mod/' . $item->mod_type . '/startattempt.php?cmid=' . $item->activityid .
                            '&sesskey=' . sesskey();
                    break;
                case "questionnaire":
                    $urltoactivity = $CFG->wwwroot . '/mod/' . $item->mod_type . '/preview.php?id=' . $item->activityid;
                    break;
                default:
                    $urltoactivity = $CFG->wwwroot . '/mod/' . $item->mod_type . '/view.php?id=' . $item->activityid;

            }
        }
        $item->activity_url = $urltoactivity;

        // Prepare sections name.
        if (empty($item->section_name) && $item->num_section != 0) {
            $item->section_name = get_string('nameemptysection', 'community_social') . ' ' . $item->num_section;
        }
    }

    $activities = array_values($activities);

    $result = array(
            'activities_learn_stat' => $activities,
            'count_learn_stat' => count($activities)
    );

    return $result;
}

function social_get_activities_from_oer_catalog($userid) {

    $activity = new \community_oer\activity_oer;
    $resactivities = $activity->query()->compareArrayField('users', 'userid', $userid)->compare('visible', '1')
            ->groupBy('cmid')->groupBy('mod_name');

    $resactivities = $activity->calculate_data_online($resactivities, 'social');
    $resactivities = array_values($resactivities->get());

    $user = social_get_user_info($userid);

    $result = array(
            'oercatalog_activities' => $resactivities,
            'count_oercatalog_activities' => count($resactivities),
            'firstname_oercatalog' => $user->firstname
    );

    return $result;
}

// Update school.
function social_update_school($userid, $school) {
    global $DB, $CFG;

    // Update school.
    $teudat = $DB->get_record('user_info_field', array('shortname' => 'school'));

    if (!empty($teudat)) {
        $res = $DB->get_record('user_info_data', array('fieldid' => $teudat->id, 'userid' => $userid));

        if (!empty($res)) {
            $res->data = $school;
            $DB->update_record('user_info_data', $res, $bulk = false);
        } else {
            $dataobject = new stdClass();
            $dataobject->userid = $userid;
            $dataobject->fieldid = $teudat->id;
            $dataobject->data = $school;
            $DB->insert_record('user_info_data', $dataobject);
        }
    }

}

// Open permission course.
function social_open_permission_course($userid, $courseid) {
    global $DB;

    $namerole = 'teachercolleague';

    $role = $DB->get_record('role', array('shortname' => $namerole));
    if (!empty($role)) {
        enrol_try_internal_enrol($courseid, $userid, $role->id);
    }
}

// Close permission course.
function social_close_permission_course($userid, $courseid) {
    global $DB;

    $namerole = 'teachercolleague';
    $role = $DB->get_record('role', array('shortname' => $namerole));

    $course = $DB->get_record('course', array('id' => $courseid));
    if (!empty($course)) {
        $context = \context_course::instance($courseid);
        if (!empty($role)) {
            if ($DB->count_records('role_assignments', array('contextid' => $context->id, "userid" => $userid)) > 1) {
                role_unassign($role->id, $userid, $context->id);
            } else {
                role_unassign($role->id, $userid, $context->id);
                $sql = "SELECT uen.id as id
            FROM {user_enrolments} uen
            LEFT JOIN {enrol} en ON (uen.enrolid=en.id)
            WHERE uen.userid=? AND en.courseid=?";
                $enrollments = $DB->get_record_sql($sql, array($userid, $courseid));
                if (!empty($enrollments)) {
                    $DB->delete_records('user_enrolments', array('id' => $enrollments->id));
                }
            }
        }
    }
}

// Approve courses to user.
function social_approve_courses_to_user($userid, $sharedcoursesids) {
    global $DB, $USER;

    if (!empty($sharedcoursesids)) {

        $arrcourses = explode(',', $sharedcoursesids);
        foreach ($arrcourses as $courseid) {
            $res = $DB->get_record('community_social_shrd_crss', array('id' => $courseid, 'userid' => $USER->id));
            if (!empty($res)) {

                // Save to community_social_collegues.
                $row = $DB->get_record('community_social_collegues',
                        array('userid' => $userid, 'social_shared_courses_id' => $courseid));
                if (!empty($row)) {
                    $row->approved = 1;
                    $DB->update_record('community_social_collegues', $row, $bulk = false);
                } else {
                    $dataobject = new stdClass();
                    $dataobject->userid = $userid;
                    $dataobject->social_shared_courses_id = $courseid;
                    $dataobject->approved = 1;
                    $dataobject->timecreated = time();
                    $dataobject->timemodified = time();
                    $DB->insert_record('community_social_collegues', $dataobject);
                }

                // Update community_social_requests.
                $req = $DB->get_record('community_social_requests',
                        array('userid' => $USER->id, 'usersendid' => $userid, 'social_shared_courses_ids' => $sharedcoursesids));
                if (!empty($req)) {
                    $req->status = 1;
                    $DB->update_record('community_social_requests', $req, $bulk = false);
                }

                social_open_permission_course($userid, $res->courseid);
            }
        }

    }
}

// Teachers.

// Get data list teachers.
function social_data_list_teachers($tab = 0, $search = '') {
    $data = array();

    $users = social_get_rellevant_teachers_for_list($search);

    $dataallteachers = social_teachers_get_all($search, $users);
    $datateachersfollowers = social_teachers_get_followers(null, $search, $users);
    $datateacherswhereyoucollegue = social_teachers_where_you_collegue(null, $search, $users);
    /*$data_collegues_teachers = social_teachers_get_collegues(null, $search, $users);
    $data_teachers_followed = social_teachers_get_followed(null, $search, $users);
    $data_teachers_jerusalem = social_teachers_get_by_city('ירושלים', $users);*/

    $tabs = array(
            0 => array(
                    'tab_id' => 0,
                    'tab_name' => get_string('allteachers', 'community_social'),
                    'tab_data' => $dataallteachers,
                    'tab_count' => count($dataallteachers),
                    'tab_count_active' => false,
                    'tab_active' => ($tab == 0) ? 'active' : '',
                    'tab_search' => $search
            ),
            1 => array(
                    'tab_id' => 1,
                    'tab_name' => get_string('colleagueteacherstab', 'community_social'),
                    'tab_collegue' => true,
                    'tab_data' => $datateacherswhereyoucollegue,
                    'tab_count' => count($datateacherswhereyoucollegue),
                    'tab_count_active' => true,
                    'tab_active' => ($tab == 1) ? 'active' : '',
                    'tab_search' => $search
            ),
            2 => array(
                    'tab_id' => 2,
                    'tab_name' => get_string('followteachers', 'community_social'),
                    'tab_data' => $datateachersfollowers,
                    'tab_count' => count($datateachersfollowers),
                    'tab_count_active' => true,
                    'tab_active' => ($tab == 2) ? 'active' : '',
                    'tab_search' => $search
            ),
            /*3 => array(
                    'tab_id' => 4,
                    'tab_name' => get_string('followedteachers', 'community_social'),
                    'tab_data' => $data_teachers_followed,
                    'tab_count' => count($data_teachers_followed),
                    'tab_count_active' => true,
                    'tab_active' => ($tab == 3) ? 'active' : '',
                    'tab_search' => $search
            ),
            4 => array(
                    'tab_id' => 4,
                    'tab_name' => get_string('teachersjerusalem', 'community_social'),
                    'tab_data' => $data_teachers_jerusalem,
                    'tab_count' => count($data_teachers_jerusalem),
                    'tab_count_active' => true,
                    'tab_active' => ($tab == 4) ? 'active' : '',
                    'tab_search' => $search
            ),
            3 => array(
                    'tab_id' => 3,
                    'tab_name' => get_string('whereyoucolleagueteacherstab', 'community_social'),
                    'tab_data' => $data_collegues_teachers,
                    'tab_count' => count($data_collegues_teachers),
                    'tab_count_active' => true,
                    'tab_active' => ($tab == 3) ? 'active' : '',
                    'tab_search' => $search
            ),*/
    );

    // Prepare cohorts tabs.
    $tabs = social_prepare_cohorts_tabs($tabs, $tab, $search, $users);

    // Default tabid if wrong tabid.
    if (!isset($tabs[$tab])) {
        $tab = 0;
    }

    // Tabs.
    $data['teachers_tabs'] = $tabs;
    $data['teachers_current_tab'] = $tabs[$tab];

    return $data;
}

// Get info by teacher for list of teachers.
function social_teachers_prepare_users_object($user) {
    global $DB, $CFG, $USER;

    $user->user_url = $CFG->wwwroot . '/local/community/plugins/social/profile.php?id=' . $user->id;
    $user->image_url = $CFG->wwwroot . '/user/pix.php/' . $user->id . '/f1.jpg';
    $user->if_colleagues = social_if_user_colleagues($user->id);
    $user->if_followers = social_if_user_followers($user->id);

    $userdetails = new classUserDetails();
    $userdetails->set_user_id($user->id);

    $user->count_oercatalog = $userdetails->get_count_oer_activities();
    $user->count_colleagues = $userdetails->get_colleagues();
    $user->count_followers = $userdetails->get_followers();
    $user->count_followed = $userdetails->get_followed();
    $user->count_shared_courses = $userdetails->get_shared_courses_in_social();
    $user->last_access = $userdetails->get_last_access();

    // Get own user's courses, where another user get approved access as collegue (only for peers tab).
    $user->peeredcourses = json_decode($userdetails->get_peered_courses());

    return $user;
}

function social_get_rellevant_teachers_for_list($search = '') {
    global $DB, $CFG, $USER;

    if (in_array(local_community_get_instancename(), array('chemistry', 'biology', 'sciences'))) {
        $where = 'WHERE 1 ';
    } else {
        $where = 'WHERE (up.total_pombim > 0 OR sud.countoercatalog > 0) ';
    }

    $search = trim($search);

    if (!empty($search)) {
        $where .= " AND (firstname LIKE('%" . $search . "%') OR  lastname LIKE('%" . $search . "%') OR  CONCAT(u.firstname,' ',u.lastname) 
         LIKE('%" . $search . "%') OR  CONCAT(u.lastname,' ',u.firstname) LIKE('%" . $search . "%')) ";
    }

    $sql = "
        SELECT
          u.id,
          u.username,
          u.firstname,
          u.lastname,
          u.email,
          u.city,
          CONCAT (u.firstname, ' ', u.lastname) AS fullname,
          CONCAT (u.lastname, ' ', u.firstname) AS unfullname,
          sud.countoercatalog AS countoercatalog,
          up.total_pombim AS total_pombim
        FROM
          (SELECT
            DISTINCT tup.userid AS id,
            COUNT(lssc.courseid) AS total_pombim
          FROM
            {user_preferences} AS tup
            LEFT JOIN {community_social_shrd_crss} lssc
              ON (lssc.userid = tup.userid)
          WHERE tup.name = 'community_social_enable'
            AND tup.value = 1
            AND tup.userid !=?
          GROUP BY id) AS up
          
          LEFT JOIN {user} u
            ON (u.id = up.id)
          LEFT JOIN {community_social_usr_dtls} sud
            ON (sud.userid = up.id)
        
        " . $where . "
        
        ORDER BY sud.colleagues DESC,
          sud.countoercatalog DESC,
          sud.followers DESC,
          sud.usedoercatalog DESC,
          sud.followed DESC,
          sud.lastaccess DESC
    ";

    return $DB->get_records_sql($sql, array($USER->id));
}

function social_get_rellevant_teachers_for_cron() {
    global $DB, $CFG, $USER;

    $where = '';

    $sql = "
        SELECT
          u.id,
          u.username,
          u.firstname,
          u.lastname,
          u.email,
          u.city,
          CONCAT(u.firstname,' ',u.lastname) AS fullname,
          CONCAT(u.lastname,' ',u.firstname) AS unfullname          
        FROM
          (SELECT
            userid AS id
          FROM
            {user_preferences}
          WHERE NAME = 'community_social_enable'
            AND VALUE = 1
            AND userid != ?) AS up
          LEFT JOIN {user} u
            ON (u.id = up.id)
          LEFT JOIN {community_social_usr_dtls} sud
            ON (sud.userid = up.id)
            
            " . $where . "
            
        ORDER BY sud.colleagues ASC,
          sud.countoercatalog DESC,
          sud.followers DESC,
          sud.usedoercatalog DESC,
          sud.followed DESC,
          sud.lastaccess DESC
    ";

    return $DB->get_records_sql($sql, array($USER->id));
}

function social_teachers_get_all($search = '', $users = null) {
    $result = array();

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list($search);
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id)) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

function social_teachers_get_collegues($userid = null, $search = '', $users = null) {
    global $USER;
    $result = array();

    if ($userid == null) {
        $userid = $USER->id;
    }

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list($search);
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id) && social_if_user_colleagues($user->id)) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

function social_teachers_where_you_collegue($userid = null, $search = '', $users = null) {
    global $USER;
    $result = array();

    if ($userid == null) {
        $userid = $USER->id;
    }

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list($search);
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id) && social_if_you_colleague_for_user($user->id)) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

function social_teachers_get_followers($userid = null, $search = '', $users = null) {
    global $USER;
    $result = array();

    if ($userid == null) {
        $userid = $USER->id;
    }

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list($search);
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id) && social_if_user_followers($user->id)) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

function social_teachers_get_followed($userid = null, $search = '', $users = null) {
    global $USER;
    $result = array();

    if ($userid == null) {
        $userid = $USER->id;
    }

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list($search);
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id) && social_if_user_followed($user->id)) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

function social_teachers_get_by_city($city, $userid = null, $users = null) {
    global $USER;
    $result = array();

    if ($userid == null) {
        $userid = $USER->id;
    }

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list();
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id) && $user->city == $city) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

// Cohort functions.

function social_teachers_from_cohort($cohortid, $search = '', $users = null) {
    $result = array();

    if ($users == null) {
        $users = social_get_rellevant_teachers_for_list($search);
    }

    foreach ($users as $user) {
        if (social_has_permission($user->id) && social_if_user_in_cohort($cohortid, $user->id)) {
            $result[] = social_teachers_prepare_users_object($user);
        }
    }
    return $result;
}

function social_if_user_in_cohort($cohortid, $userid = null) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    $sql = "
        SELECT *
        FROM {cohort} c
        LEFT JOIN {cohort_members} cm ON(cm.cohortid=c.id)
        WHERE c.id=? AND  cm.userid=? AND c.visible=1     
    ";
    $row = $DB->get_record_sql($sql, array($cohortid, $userid));

    if (!empty($row)) {
        return true;
    }
    return false;
}

function social_get_cohorts_by_user($userid = null) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    $sql = "
        SELECT c.*
        FROM {cohort_members} cm
        LEFT JOIN {cohort} c ON(cm.cohortid=c.id)
        WHERE cm.userid=? AND c.visible=1    
    ";
    $rows = $DB->get_records_sql($sql, array($userid));

    return $rows;
}

function social_prepare_cohorts_tabs($tabs, $tab, $search, $users = null) {

    $counttabs = count($tabs);
    $cohorts = social_get_cohorts_by_user();
    $cohorts = social_cohorts_via_settings($cohorts);

    foreach ($cohorts as $cohort) {
        $data = social_teachers_from_cohort($cohort->id, $search, $users);

        $tabs[$counttabs] = array(
                'tab_id' => $counttabs,
                'tab_name' => $cohort->name,
                'tab_data' => $data,
                'tab_count' => count($data),
                'tab_count_active' => true,
                'tab_active' => ($tab == $counttabs) ? 'active' : '',
                'tab_search' => $search
        );
        $counttabs++;
    }

    return $tabs;
}

function social_cohorts_via_settings($cohorts) {

    $result = array();
    $cohortcategoryid = \community_oer\main_oer::get_oer_category();

    if (!isset($cohortcategoryid) || empty($cohortcategoryid)) {
        return $result;
    }

    foreach ($cohorts as $key => $cohort) {
        if ($cohort->contextid == $cohortcategoryid) {
            $result[$key] = $cohort;
        }
    }

    return $result;
}

// Send message to user.
function social_send_message_to_teacher($useridfrom, $useridto, $sharedcourseid, $component, $eventtype, $customdata = array()) {
    global $DB, $CFG;

    $smallmessage = get_string($component . '_' . $eventtype, 'message_petel');

    $time = time();
    $userfrom = $DB->get_record("user", array('id' => $useridfrom));

    $customdata['custom'] = true;
    $customdata[$eventtype] = true;
    $customdata['firstname'] = $userfrom->firstname;
    $customdata['lastname'] = $userfrom->lastname;
    $customdata['teacher_image'] = $CFG->wwwroot . '/user/pix.php/' . $useridfrom . '/f1.jpg';
    $customdata['dateformat'] = date("d.m.Y", $time);
    $customdata['timeformat'] = date("H:i", $time);

    // Prepare course.
    if (!empty($sharedcourseid)) {
        $row = $DB->get_record('community_social_shrd_crss', array('id' => $sharedcourseid));
        try {
            $course = get_course($row->courseid);
        } catch (Exception $e) {
            $course = new stdClass();
            $course->fullname = '';
            $course->id = '';
        }

        $customdata['coursename'] = $course->fullname;
        $customdata['courseurl'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
    }

    $objinsert = new stdClass();
    $objinsert->useridfrom = $useridfrom;
    $objinsert->useridto = $useridto;

    $objinsert->subject = $smallmessage;
    $objinsert->fullmessage = $smallmessage;
    $objinsert->fullmessageformat = 2;
    $objinsert->fullmessagehtml = '';
    $objinsert->smallmessage = $smallmessage;
    $objinsert->component = $component;
    $objinsert->eventtype = $eventtype;
    $objinsert->timecreated = $time;
    $objinsert->customdata = json_encode($customdata);

    $notificationid = $DB->insert_record('notifications', $objinsert);

    $objinsert = new stdClass();
    $objinsert->notificationid = $notificationid;
    $DB->insert_record('message_petel_notifications', $objinsert);

    return $notificationid;
}

function social_migrate_course($oldcourseid, $newcourseid) {
    global $DB;

    // Shared courses.
    foreach ($DB->get_records('community_social_shrd_crss', array('courseid' => $oldcourseid)) as $shared) {
        $usersrecache = [];
        $userspermission = [];

        // Users for recache.
        $usersrecache[] = $shared->userid;

        $shared->courseid = $newcourseid;
        $DB->update_record('community_social_shrd_crss', $shared);

        // Collegues.
        $colleques = $DB->get_records('community_social_collegues', array('social_shared_courses_id' => $shared->id));

        foreach ($colleques as $colleque) {

            // Users for change permission.
            if ($colleque->approved) {
                $userspermission[] = $colleque->userid;
            }

            // Users for recache.
            $usersrecache[] = $colleque->userid;
        }

        // Request.
        $requests = $DB->get_records('community_social_requests', array('social_shared_courses_ids' => $shared->id));

        foreach ($requests as $request) {

            // Delete row.
            if (!$request->status) {
                $DB->delete_records('community_social_requests', array('id' => $request->id));

                // Remove messages.
                $DB->delete_records('message_petel_notifications', array('notificationid' => $request->messageid));

                continue;
            }

            // Users for recache.
            $usersrecache[] = $request->userid;
            $usersrecache[] = $request->usersendid;
        }

        // Set new permissions.
        $userspermission = array_unique($userspermission);
        foreach ($userspermission as $userid) {
            social_close_permission_course($userid, $oldcourseid);
            social_open_permission_course($userid, $newcourseid);
        }

        // Rechache.
        $usersrecache = array_unique($usersrecache);
        foreach ($usersrecache as $userid) {
            \classUserDetails::activate_refresh($userid);
        }
    }
}
