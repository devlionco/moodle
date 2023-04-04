<?php

namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * Simple task to delete user accounts for users who have not confirmed in time.
 */
class auto_confirm_users_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('autoconfirmuserstask', 'local_petel');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute()
    {
        global $CFG, $DB;

            // confirm users who haven't confirmed within required period.
            if (!empty($CFG->autoconfirmusers)) {
                $rs = $DB->get_recordset_sql("SELECT *
                                             FROM {user}
                                            WHERE confirmed = 0 AND deleted = 0");
                foreach ($rs as $user) {
                    $DB->set_field('user', 'confirmed', '1', ['id' => $user->id]);
                    mtrace("auto confirm user  " . fullname($user, true) . " ($user->id)");
                }
                $rs->close();
            }
        }
    }
