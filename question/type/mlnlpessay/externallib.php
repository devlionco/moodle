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
 * Format multitopicmoe external API
 *
 * @package    qtype_mlnlpessay
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once $CFG->dirroot . '/question/type/rendererbase.php';
require_once $CFG->dirroot . '/question/type/mlnlpessay/renderer.php';
require_once $CFG->dirroot . '/mod/quiz/locallib.php';



class qtype_mlnlpessay_external extends external_api {

    public static function get_feedback($questionid, $questionattemptid) {
        global $DB, $USER, $PAGE;

        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(
            self::get_feedback_parameters(),
            array(
                'questionid' => $questionid,
                'questionattemptid' => $questionattemptid,
            )
        );

        $questionid = $params['questionid'];
        $questionattemptid = $params['questionattemptid'];

        $result = [];
        $response = '';

        if ($attemptinfo = $DB->get_record_sql("SELECT qa.id, qua.slot FROM {question_attempts} qua 
                JOIN {quiz_attempts} qa ON qua.questionusageid = qa.uniqueid WHERE qua.id = ?", [$questionattemptid])) {
            $attemptobj = \quiz_attempt::create($attemptinfo->id);
            $qa = $attemptobj->get_question_attempt($attemptinfo->slot);

            $renderer = new qtype_mlnlpessay_renderer($PAGE, '');
            $response = $renderer->specific_feedback($qa, false);
        }

        $result['response'] = json_encode($response);
        $result['status'] = !empty($response);
        return $result;
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_feedback_parameters() {
        return new external_function_parameters(
            array(
                'questionid' => new external_value(PARAM_INT, 'questionid'),
                'questionattemptid' => new external_value(PARAM_INT, 'questionattemptid'),
            )
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_feedback_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function set_override_parameters() {
        return new external_function_parameters(
            array(
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'questionid' => new external_value(PARAM_INT, 'questionid'),
                'questionattemptid' => new external_value(PARAM_INT, 'questionattemptid'),
            )
        );
    }

    public static function set_override($categoryid, $questionid, $questionattemptid) {
        global $DB, $USER, $PAGE;

        $params = self::validate_parameters(
            self::set_override_parameters(),
            array(
                'categoryid' => $categoryid,
                'questionid' => $questionid,
                'questionattemptid' => $questionattemptid,
            )
        );

        $categoryid = $params['categoryid'];
        $questionid = $params['questionid'];
        $questionattemptid = $params['questionattemptid'];

        $result = ['status' => true, 'error' => ''];
        $response = $error = '';
        $toregrade = false;

        try {
            $pythonfeedbacksql = check_response($questionid, $questionattemptid);
            if ($pythonfeedbacksql && !empty($pythonfeedbacksql->pythonresponse)) {
                $fraction = 0;
                $pythonresponse = json_decode($pythonfeedbacksql->pythonresponse);
                $enabledcategoriesids = [];
                if ($enabledcategories = get_enabled_categories($questionid)) {
                    $enabledcategoriesids = array_keys($enabledcategories);
                }

                foreach ($pythonresponse as $key => $response) {
                    if ($response->id == $categoryid) {
                        $pythonresponse[$key]->correct = $response->correct ? 0 : 1;
                        $pythonresponse[$key]->overridden = isset($response->overridden) && !empty($response->overridden) ? 0 : 1;

                        $toregrade = true;
                    }

                    if (in_array($response->id, $enabledcategoriesids)) {
                        $fraction += (int) $enabledcategories[$response->id]->weight * (int) $pythonresponse[$key]->correct / 100;
                    }
                }

                $pythonfeedbacksql->pythonresponse = json_encode($pythonresponse);
                $DB->update_record('qtype_mlnlpessay_response', $pythonfeedbacksql);

                if ($toregrade) {
                    ob_start();
                    \qtype_mlnlpessay\task\adhoc_graderesponse::regrade_attempt_by_questionattempt($questionattemptid, $fraction);
                    ob_end_clean();
                }
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $response['error'] = $e->getMessage();
        }


        if ($result['status']) {
            $result = array_replace($result, static::get_feedback($questionid, $questionattemptid));
        }

        return $result;
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function set_override_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'response' => new external_value(PARAM_RAW, 'response'),
                'error' => new external_value(PARAM_RAW, 'error text')
            )
        );
    }
}