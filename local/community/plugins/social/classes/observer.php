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
 * Event observers for community_social
 *
 * @package    community_social
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_social;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/community/plugins/social/classes/classMessageFollowers.php');
require_once($CFG->dirroot . '/local/community/plugins/social/classes/classUserDetails.php');

/**
 * community_social observers class
 */
class observer {

    /**
     * course_module_visibility_changed
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function course_module_visibility_changed($event) {
        global $CFG, $COURSE, $DB, $USER;
        require_once($CFG->libdir . "/coursecatlib.php");

        $eventdata = $event->get_data();

        // Trigger jbserver for social.
        $userid = \local_metadata\mcontext::module()->get($eventdata['objectid'], 'userid');

        \classUserDetails::activate_refresh($userid);

        // Trigger observer only if cm is shown (opened).
        if ($eventdata['other']['action'] == 'show') {
            $coursecat = \core_course_category::get($COURSE->category);
            $coursecatstree = $coursecat->get_parents();
            array_push($coursecatstree, $COURSE->category);
            if (in_array(local_community_get_oercatalog_categoryid(), $coursecatstree)) {

                // Find userid of the user, who has shared cm (activity) to the oer catalog.
                $sharinguserid = \local_metadata\mcontext::module()->get($eventdata['objectid'], 'userid');
                $sharinguserid = !empty($sharinguserid) ? $sharinguserid : $USER->id; // Fallback to current user id.

                // Sending notification to following teachers.
                $classfollowers = new \MessageFollowers($sharinguserid, $eventdata['objectid'], $eventdata['courseid']);
                $classfollowers->sendMessageToFollowers();
            }
        }
    }

    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        $eventdata = $event->get_data();
        $courseid = $eventdata['objectid'];

        $users = array();

        $data = $DB->get_records('community_social_shrd_crss', array('courseid' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $DB->delete_records('community_social_shrd_crss', array('id' => $item->id));
        }

        $data = $DB->get_records('community_social_collegues', array('social_shared_courses_id' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $DB->delete_records('community_social_collegues', array('id' => $item->id));
        }

        $data = $DB->get_records('community_social_requests', array('social_shared_courses_ids' => $courseid));
        foreach ($data as $item) {
            $users[] = $item->userid;
            $users[] = $item->usersendid;
            $DB->delete_records('community_social_requests', array('id' => $item->id));
        }

        $users = array_unique($users);

        // Refresh users.
        $userdetails = new \classUserDetails();

        foreach ($users as $userid) {
            $userdetails->set_user_id($userid);
            $userdetails->update_teacher();
        }
    }

    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        $eventdata = $event->get_data();
        $userid = $eventdata['objectid'];

        $DB->delete_records('community_social_collegues', array('userid' => $userid));

        $DB->delete_records('community_social_followers', array('userid' => $userid));
        $DB->delete_records('community_social_followers', array('followuserid' => $userid));

        $DB->delete_records('community_social_requests', array('userid' => $userid));
        $DB->delete_records('community_social_requests', array('usersendid' => $userid));

        $DB->delete_records('community_social_shrd_crss', array('userid' => $userid));

        $DB->delete_records('community_social_usr_dtls', array('userid' => $userid));

        // Refresh all users.
        $userdetails = new \classUserDetails();

        $data = $DB->get_records('community_social_usr_dtls');
        foreach ($data as $item) {
            $userdetails->set_user_id($item->userid);
            $userdetails->update_teacher();
        }
    }

    public static function course_module_updated(\core\event\course_module_updated $event) {

        $data = self::data_from_cache_oer_activity_by_id($event->objectid);
        foreach ($data as $item) {

            // Refresh user in cron.
            \classUserDetails::activate_refresh($item->userid);
        }
    }

    public static function course_module_deleted(\core\event\course_module_deleted $event) {

        $data = self::data_from_cache_oer_activity_by_id($event->objectid);
        foreach ($data as $item) {

            // Refresh user in cron.
            \classUserDetails::activate_refresh($item->userid);
        }
    }

    public static function update_metadata(\local_metadata\event\update_metadata $event) {

        if ($event->contextlevel == CONTEXT_COURSE) {
            $data = self::data_from_cache_oer_activity_by_courseid($event->objectid);
            foreach ($data as $item) {

                // Refresh user in cron.
                \classUserDetails::activate_refresh($item->userid);
            }
        }

        if ($event->contextlevel == CONTEXT_MODULE) {
            $data = self::data_from_cache_oer_activity_by_id($event->objectid);
            foreach ($data as $item) {

                // Refresh user in cron.
                \classUserDetails::activate_refresh($item->userid);
            }
        }
    }

    private static function data_from_cache_oer_activity_by_id($activityid) {
        global $DB;

        $str = '"activity_id":"' . $activityid . '"';

        $query = "
            SELECT *
            FROM {community_social_usr_dtls}
            WHERE dataoercatalog LIKE('%" . $DB->sql_like_escape($str) . "%')
        ";

        return $DB->get_records_sql($query);
    }

    private static function data_from_cache_oer_activity_by_courseid($courseid) {
        global $DB;

        $str = '"course_id":"' . $courseid . '"';

        $query = "
            SELECT *
            FROM {community_social_usr_dtls}
            WHERE dataoercatalog LIKE('%" . $DB->sql_like_escape($str) . "%')
        ";

        return $DB->get_records_sql($query);
    }
}
