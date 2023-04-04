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
 * Local plugin "petel" - Task definition
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_petel update user engagement stats task class.
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_stats_usertimespent extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'local_petel';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'petel_cron_stats';
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_petel_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_adhoc_stats();
            $lock->release();
        }
    }

    public function run_adhoc_stats() {
        global $DB, $CFG;

        raise_memory_limit(MEMORY_UNLIMITED);

        // Support PHP72 missing array_key_first & array_key_last functions
        if (!function_exists(‘array_key_first’)) {

            function array_key_first(array $arr) {
                foreach($arr as $key => $unused) {
                    return $key;
                }
                return NULL;
            }
            function array_key_last(array $arr) {
                foreach($arr as $key => $unused) {
                    $lastkey = $key;
                }
                return $lastkey;
            }
        }

        // Get the last time we processed any user.
        $lasttimeprocessed = $DB->get_record_sql(
            'SELECT timecreated 
             FROM {stats_user_timespent} ORDER BY timecreated DESC LIMIT 1 ');

        if (!$lasttimeprocessed) {
            $lasttimeprocessed = 0;
        } else {
            $lasttimeprocessed = $lasttimeprocessed->timecreated;
        }

        // Get all events that follow last time we processed anyone.
        $sql_users_to_process = 'SELECT * FROM {logstore_standard_log} WHERE timecreated > :lasttimeprocessed ';
        $userevents_to_process = $DB->get_records_sql($sql_users_to_process,
            array('lasttimeprocessed' => $lasttimeprocessed), 0, 50000);

        // Process each user
        // get time delta between current event and last event (if smaller than max $CFG->sessiontimeout )
        foreach ($userevents_to_process as $user_event) {
            $sql_user_events = 'SELECT * FROM {logstore_standard_log} 
                                WHERE userid = :userid AND id <= :lastid 
                                ORDER BY id DESC LIMIT 2 ';
            $user_events = $DB->get_records_sql($sql_user_events,
                ['userid' => $user_event->userid, 'lastid' => $user_event->id]);

            // first key = latest/current user event.
            $time_spent = $user_events[array_key_first($user_events)]->timecreated -
                $user_events[array_key_last($user_events)]->timecreated;

            // Add stats to mdl_stats_user_timespent
            $userkey = array_key_first($user_events);
            $user_timespent_record = [
                'userid' => $user_events[$userkey]->userid
                ,'courseid' => $user_events[$userkey]->courseid
                ,'contextid' => $user_events[$userkey]->contextid
                ,'timecreated' => $user_events[$userkey]->timecreated
                ,'timespent' => $time_spent
            ];
            if ($CFG->sessiontimeout > $time_spent && $time_spent !== 0 ) {
                $ok = $DB->insert_record('stats_user_timespent', $user_timespent_record);
            }
        }
    }
}