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
 * The local_petel calculate social relationships task class.
 *
 * @package    local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calculate_social_relationships extends \core\task\scheduled_task {

    const DEFAULT_BULK_USER_PREFIX = 'bulkuser';
    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('calculatesocialrelationships', 'local_petel');
    }

    /**
     * Execute scheduled task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG, $DB;
        require_once(__DIR__ . '/../../locallib.php');

        local_petel_calculate_social_relationships();

        return true;
    }
}
