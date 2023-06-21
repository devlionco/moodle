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
 * External Web Service class
 *
 * @package    theme_petel
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

/**
 * External functions for theme petel.
 *
 * @package     theme_petel
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_petel_external extends external_api
{

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function student_question_message_parameters() {
        return new external_function_parameters(
            array(
                'fromuserid' => new external_value(PARAM_INT, 'From user id'),
                'touserid' => new external_value(PARAM_INT, 'T user id'),
                'questionid' => new external_value(PARAM_INT, 'Question ID', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Returns get_activity_grade_status
     * @return string
     */
    public static function student_question_message($fromuserid, $touserid, $questionid = null) {
        global $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);

        $params = self::validate_parameters(self::student_question_message_parameters(),
            array(
                'fromuserid' => (int)$fromuserid,
                'touserid' => (int)$touserid,
                'questionid' => (int)$questionid,
            )
        );

        // Event data.
        if ($questionid) {
            $eventdata = array(
                'fromuserid' => $fromuserid,
                'touserid' => $touserid,
                'questionid' => $questionid,
            );
        } else {
            $eventdata = array(
                'fromuserid' => $fromuserid,
                'touserid' => $touserid,
            );
        }

        \theme_petel\event\quiz_student_question::create_event($eventdata)->trigger();

        return '';
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function student_question_message_returns() {

        return new external_value(PARAM_RAW, 'Status');
    }
}
