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
 * AlgebraKiT question definition class.
 *
 * @package    qtype
 * @subpackage numerical
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/algebrakit/lib.php');

class SessionResponse {
    public $success;
    public $msg;
    public $sessions;
}

/**
 * Represents a numerical question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebrakit_question extends question_graded_automatically {
    /** @var array of question_answer. */
    public $answers = array();

    public $exercise_id;
    public $major_version;
    public $question_id;

    public $session;
    public $continued = false;

    protected $apiKey = null;

    /** @var qtype_algebrakit_answer_processor */
    public $ap;

    public function __construct() {
        $this->apiKey = get_config('qtype_algebrakit', 'apikey');
    }

    public function get_expected_data() {
        return array('_session' => PARAM_RAW_TRIMMED);
    }

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->session = json_decode($step->get_qt_var('_session'));
        $this->defaultmark = (int) $step->get_qt_var('_marksTotal');

        if ($this->session !== null) {
            //Create exercise tag and continue
            $this->continued = true;
        }
        else {
            $this->createSession($this->exercise_id, $this->major_version);
            $step->set_qt_var('_session', json_encode($this->session));
            $this->defaultmark = 0;
            if (isset($this->session) && is_array($this->session)) {
                $this->defaultmark = 0;
                foreach ($this->session as $sess) {
                    if ($sess->success == false) {
                        continue;
                    }
                    foreach ($sess->sessions as $s) {
                        $this->defaultmark += $s->marksTotal;
                    }
                }
            }
            $step->set_qt_var('_marksTotal', json_encode($this->defaultmark));
        }
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->session = json_decode($step->get_qt_var('_session'));
        $this->defaultmark = (int) $step->get_qt_var('_marksTotal');
        if ($this->session != null) {
            $this->continued = true;
        }
        parent::apply_attempt_state($step);
    }

    public function createSession($exerciseId, $majorVersion) {
        if (empty($this->apiKey)) {
            $sess = new SessionResponse();
            $sess->success = false;
            $sess->msg = 'No API Key is set. Go to the settings for the AlgebraKiT plugin to enter an API Key.';
            $sess->sessions = [];
            $this->session = [
                $sess
            ];
            return;
        }
        $exList = [
            0 => [
                'exerciseId' => $exerciseId,
                'version' => intval($majorVersion) ? intval($majorVersion) : 'latest'
            ]
        ];
        $data = array(
            'apiVersion' => 2,
            'exercises' => $exList
        );
        $this->session = akitPost('/session/create', $data, $this->apiKey);
    }

    public function summarise_response(array $response) {
        if (isset($response['_session'])) {
            return "View the indivudual questions to see the responses";
        } else {
            return null;
        }
    }

    public function un_summarise_response(string $summary) {
        if (!empty($summary)) {
            return ['_session' => $summary];
        } else {
            return [];
        }
    }

    public function is_gradable_response(array $response) {
        return true;
    }

    public function is_complete_response(array $response) {
        return true;
    }

    public function get_validation_error(array $response) {
        return '';
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return false;
    }

    public function get_correct_response() {
        return ["solutionMode"];
    }

    public function get_right_answer_summary() {
        return "Open the exercise to view the correct answer";
    }

    public function grade_response(array $response) {
        $sessionObj = json_decode($response["_session"]);
        $sessionId = $sessionObj[0]->sessions[0]->sessionId;
        $data = array(
            'sessionId' => $sessionId
        );
        $scoreObj = akitPost('/session/score', $data, $this->apiKey);
        if (isset($scoreObj->success) && $scoreObj->success === false) {
            //throw new coding_exception("Invalid response when getting score for question", "Score Response: ".json_encode($scoreObj).";\nSession info: ".$response['_session']);
            $fraction = 0;
        }
        else {
            $fraction = $scoreObj->scoring->marksEarned / $scoreObj->scoring->marksTotal;
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        // TODO.
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}