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
 * Defines the editing form for the diagnosticadv question type.
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Diagnostic ADV question editing form definition.
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadv_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE;

        $mform->addElement('advcheckbox', 'hidemark',
                get_string('hidemark', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'security',
            get_string('security', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'required',
            get_string('required', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'usecase',
            get_string('usecase', 'qtype_diagnosticadv'));

        $mform->addElement('advcheckbox', 'anonymous',
            get_string('anonymous', 'qtype_diagnosticadv'));

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_diagnosticadv', '{no}'),
                question_bank::fraction_options());

        $this->add_interactive_settings();
    }

    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_diagnosticadv');
    }


    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        return $question;
    }

    public function data_postprocessing($data) {
        if ($data['qtype'] == 'diagnosticadv') {
            $data['qtype'] = 'diagnosticadv';
        }

        return $data;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;
        $maxgrade = false;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== '') {
                $answercount++;
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 ||
                    !html_is_blank($data['feedback'][$key]['text'])) {
                $errors["answeroptions[{$key}]"] = get_string('answermustbegiven', 'qtype_diagnosticadv');
                $answercount++;
            }
        }
        if ($answercount==0) {
            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_diagnosticadv', 1);
        }
        if ($maxgrade == false) {
            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
        }
        return $errors;
    }

    public function qtype() {
        return 'diagnosticadv';
    }

    /**
     * Get the list of form elements to repeat, one for each answer.
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param $repeatedoptions reference to array of repeated options to fill
     * @param $answersoption reference to return the name of $question->options
     *      field holding an array of answers
     * @return array of form fields.
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
        &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $answeroptions = array();
        $answeroptions[] = $mform->createElement('advcheckbox', 'custom', get_string('custom', 'qtype_diagnosticadv'));
        $answeroptions[] = $mform->createElement('text', 'answer',
            $label, array('size' => 40));
        $answeroptions[] = $mform->createElement('select', 'fraction',
            get_string('gradenoun'), $gradeoptions);
        $repeated[] = $mform->createElement('group', 'answeroptions',
            $label, $answeroptions, null, false);
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }
}
