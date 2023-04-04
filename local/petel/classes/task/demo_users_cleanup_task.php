<?php
/**
 * @package    local
 * @subpackage petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author  2022 Devlion Ltd. <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_petel cache task class.
 *
 * @package    local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo_users_cleanup_task extends \core\task\scheduled_task {


    const DEFAULT_BULK_USER_PREFIX = 'bulkuser';
    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('democleanuptask', 'local_petel');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {

        global $CFG, $DB;
        require_once(__DIR__ . '/../../locallib.php');

        $bulkuserprefix = $CFG->local_petel_prefix_bulk_user ?? static::DEFAULT_BULK_USER_PREFIX;
        $params = ['username' => $DB->sql_like_escape($bulkuserprefix) . '%'];
        $pluginman = \core_plugin_manager::instance();
        $users = $DB->get_records_select('user', 'deleted = 0 AND ' . $DB->sql_like('username', ':username', false, false), $params);
        foreach ($users as $user) {
            $isexpired = local_petel_logout_by_session_timeout_per_user($user->id, false, false);
            $user_enrolments = $DB->get_records('user_enrolments', ['userid' => $user->id]);
            foreach ($user_enrolments as $user_enrolment) {
                $instance = $DB->get_record('enrol', ['id' => $user_enrolment->enrolid]);
                $plugin = enrol_get_plugin($instance->enrol);
                //if session exist or if seession have been killed
                if ($isexpired || (!empty($CFG->sessiontimeout) && $user_enrolment->timemodified + $CFG->sessiontimeout < time())) {
                    $plugin->unenrol_user($instance, $user_enrolment->userid);
                    mtrace("Unenrol user id: " . $user_enrolment->userid);
                }
            }
        }
    }
}
