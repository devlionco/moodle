<?php
/**
 * Privacy Subsystem implementation for block_activity_modules.
 *
 * @package    atto_bdi
 * @copyright  2022 The Open University of Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace atto_bdi\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for atto_bdi implementing null_provider.
 *
 * @copyright  2022 The Open University of Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}
