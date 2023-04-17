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
 * Local plugin "sandbox" - Task definition
 *
 * @package    community_social
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_social\task;

use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_social
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_social extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_social';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/local/community/plugins/social/locallib.php');

        $lockkey = 'social_cron';
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_social_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_social();
        }
        $lock->release();
    }

    public function run_cron_social() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/local/community/plugins/social/classes/classUserDetails.php');

        $timeneedupdate = time() - 1 * 24 * 60 * 60;

        $sql = "
            SELECT *
            FROM {community_social_usr_dtls}
            WHERE ifupdate = 1 OR lastupdate < ? OR ifupdate IS NULL
        ";
        $data = $DB->get_records_sql($sql, array($timeneedupdate));

        $userdetails = new \classUserDetails();
        foreach ($data as $item) {
            $userdetails->set_user_id($item->userid);
            $userdetails->update_teacher();
        }

        // Refresh if not exists users in community_social_usr_dtls.
        $usersfromdb = array();

        $data = $DB->get_records('community_social_usr_dtls');
        foreach ($data as $item) {
            $usersfromdb[] = $item->userid;
        }

        $relevatusers = social_get_rellevant_teachers_for_cron();
        foreach ($relevatusers as $user) {
            if (!in_array($user->id, $usersfromdb)) {
                $userdetails->set_user_id($user->id);
                $userdetails->update_teacher();
            }
        }

        // Data of view course by social users.
        $course = new \community_oer\course_oer;
        $cache = [];

        $oercourses = $course->query()->compare('visible', '1')->get();
        foreach ($oercourses as $course) {

            $sql = "
                SELECT *
                FROM {logstore_standard_log}
                WHERE component = 'core' AND action = 'viewed' AND target = 'course' 
                AND contextinstanceid = ? AND userid IN(" . implode(',', $usersfromdb) . ")        
            ";

            $arr = [];
            foreach ($DB->get_records_sql($sql, [$course->cid]) as $k) {
                $arr[] = $k->userid;
            }

            $cache[$course->cid] = array_unique($arr);
        }

        set_config('cache_viewed_oercourses', json_encode($cache), 'community_social');
    }
}
