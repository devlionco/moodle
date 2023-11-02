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
 * Question type class for the diagnostic ADV question type.
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/diagnosticadv/question.php');

class qtype_diagnosticadv_answer extends question_answer {
    public $custom;
    /**
     * Constructor.
     * @param int $id the answer.
     * @param string $answer the answer.
     * @param int $answerformat the format of the answer.
     * @param number $fraction the fraction this answer is worth.
     * @param string $feedback the feedback for this answer.
     * @param int $feedbackformat the format of the feedback.
     * @param integer $blankindex
     */
    public function __construct($id, $answer, $fraction, $feedback, $feedbackformat, $custom) {
        parent::__construct($id, $answer, $fraction, $feedback, $feedbackformat);
        $this->custom = $custom;
    }
}


/**
 * The diagnostic ADV question type.
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadv extends question_type {

    public function extra_question_fields() {
        return array('qtype_diagnosticadv_options', 'security', 'hidemark', 'usecase', 'required', 'anonymous');
    }

    public function extra_answer_fields() {
        return array('qtype_diagnosticadv_answers', 'custom');
    }

    protected function is_extra_answer_fields_empty($questiondata, $key) {

        return !isset($questiondata->custom[$key]) || empty($questiondata->custom[$key]);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();

        // Perform sanity checks on fractional grades.
        $maxfraction = -1;
        foreach ($question->answer as $key => $answerdata) {
            if ($question->fraction[$key] > $maxfraction) {
                $maxfraction = $question->fraction[$key];
            }
        }

        if ($maxfraction != 1) {
            $result->error = get_string('fractionsnomax', 'question', $maxfraction * 100);
            return $result;
        }

        $extraquestionfields = $this->extra_question_fields();

        if (is_array($extraquestionfields)) {
            $question_extension_table = array_shift($extraquestionfields);

            $function = 'update';
            $questionidcolname = $this->questionid_column_name();
            $options = \qtype_diagnosticadv\options::get_record([$questionidcolname => $question->id]);
            if (!$options) {
                $function = 'create';
                $options = new \qtype_diagnosticadv\options();
                $options->set($questionidcolname, $question->id);
            }
            foreach ($extraquestionfields as $field) {
                if (property_exists($question, $field)) {
                    $options->set($field, $question->$field);
                }
            }
            $options->$function();
        }

        $this->save_question_answers($question);

        $this->save_hints($question);
    }

    /**
     * Save the answers, with any extra data.
     *
     * Questions that use answers will call it from {@link save_question_options()}.
     * @param object $question  This holds the information from the editing form,
     *      it is not a standard question object.
     * @return object $result->error or $result->notice
     */
    public function save_question_answers($question) {
        global $DB;

        $context = $question->context;
        $oldanswers = $DB->get_records('question_answers',
            array('question' => $question->id), 'id ASC');

        // We need separate arrays for answers and extra answer data, so no JOINS there.
        $extraanswerfields = $this->extra_answer_fields();
        $isextraanswerfields = is_array($extraanswerfields);
        $extraanswertable = '';
        $oldanswerextras = array();
        if ($isextraanswerfields) {
            $extraanswertable = array_shift($extraanswerfields);
            if (!empty($oldanswers)) {
                $oldanswerextras = \qtype_diagnosticadv\answers::get_records_select('answerid IN (SELECT id FROM {question_answers} WHERE question = ?)', [$question->id]);
            }
        }

        // Insert all the new answers.
        foreach ($question->answer as $key => $answerdata) {
            // Check for, and ignore, completely blank answer from the form.
            if ($this->is_answer_empty($question, $key)) {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            $answer = $this->fill_answer_fields($answer, $question, $key, $context);
            $DB->update_record('question_answers', $answer);

            if ($isextraanswerfields) {
                // Check, if this answer contains some extra field data.
                if ($this->is_extra_answer_fields_empty($question, $key)) {
                    continue;
                }

                $answerextra = array_shift($oldanswerextras);
                $answerextradata = new stdClass();
                if (!$answerextra) {
                    $answerextradata->answerid = $answer->id;
                    // Avoid looking for correct default for any possible DB field type
                    // by setting real values.
                    $answerextradata = $this->fill_extra_answer_fields($answerextradata, $question, $key, $context, $extraanswerfields);
                    $answerclass = new \qtype_diagnosticadv\answers(0, $answerextradata);
                    $answerextradata->id = $answerclass->create()->get('id');
                } else {
                    // Update answerid, as record may be reused from another answer.
                    $answerextradata->answerid = $answer->id;
                    $answerextradata = $this->fill_extra_answer_fields($answerextradata, $question, $key, $context, $extraanswerfields);
                    $answerextra->from_record($answerextradata);
                    $answerextra->update();
                }
            }
        }

        if ($isextraanswerfields) {
            // Delete any left over extra answer fields records.
            $oldanswerextraids = array();
            foreach ($oldanswerextras as $oldextra) {
                $oldextra->delete();
            }
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
    }

    protected function initialise_question_answers(question_definition $question,
        $questiondata, $forceplaintextanswers = true) {
        $question->answers = array();
        if (empty($questiondata->options->answers)) {
            return;
        }
        foreach ($questiondata->options->answers as $a) {

            $question->answers[$a->id] = new qtype_diagnosticadv_answer($a->id, $a->answer,
                $a->fraction, $a->feedback, $a->feedbackformat, $a->custom);
        }
        /*
        var_dump($question);
        var_dump($questiondata->options->answers);
        die();
        */
    }


    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return $answer->fraction;
            }
        }
        return 0;
    }

    public function get_possible_responses($questiondata) {
        $responses = array();

        $starfound = false;
        foreach ($questiondata->options->answers as $aid => $answer) {
            $responses[$aid] = new question_possible_response($answer->answer,
                    $answer->fraction);
            if ($answer->answer === '*') {
                $starfound = true;
            }
        }

        if (!$starfound) {
            $responses[0] = new question_possible_response(
                    get_string('didnotmatchanyanswer', 'question'), 0);
        }

        $responses[null] = question_possible_response::no_response();

        return array($questiondata->id => $responses);
    }
}
