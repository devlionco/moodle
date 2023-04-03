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
 * Defines the editing form for the numerical question type.
 *
 * @package    qtype
 * @subpackage numerical
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/algebrakit/questiontype.php');


/**
 * numerical editing form definition.
 *
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebrakit_edit_form extends question_edit_form {
    /** @var int we always show at least this many sets of unit fields. */
    const UNITS_MIN_REPEATS = 1;
    const UNITS_TO_ADD = 2;

    protected $ap = null;

    protected function definition_inner($mform) {
        $this->add_exercise_options($mform);
    }

    /**
     * Add the unit handling options to the form.
     * @param object $mform the form being built.
     */
    protected function add_exercise_options($mform) {

        $i = array_search("questiontext", $mform->_required);
        array_splice($mform->_required, $i, 1);
        $mform->_rules['questiontext'] = array();

        $mform->addElement('header', 'akit_exercise',
                get_string('akit_exercise', 'qtype_algebrakit'));

        $mform->addElement('text', 'exercise_id',
                get_string('exerciseid', 'qtype_algebrakit'));

        $mform->addElement('text', 'major_version',
                get_string('majorversion', 'qtype_algebrakit'));

        $mform->setType('exercise_id', PARAM_NOTAGS);
        $mform->setType('major_version', PARAM_INT);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->validate_exercise_id($data, $errors);
        $errors = $this->validate_major_version($data, $errors);
        return $errors;
    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_options($question);
        return $question;
    }

    public function data_preprocessing_options($question) {
        if (!isset($question->options)) {
            return $question;
        }
        $opt = $question->options;
        $question->exercise_id = $opt->exercise_id;
        $question->major_version = $opt->major_version;
        $question->question_id = $opt->question_id;
        return $question;
    }

    protected function validate_exercise_id($data, $errors) {
        //Check exercise ID
        $exerciseId = $data['exercise_id'];
        if (!isset($exerciseId) || trim($exerciseId) === '') {
            $errors['exerciseId'] = get_string('exerciseIdRequired', 'qtype_algebrakit');
        }
        return $errors;
    }

    protected function validate_major_version($data, $errors) {
        //Check major version
        $majorVersion = $data['major_version'];
        if (!isset($majorVersion) || trim($majorVersion) === '') {
            $errors['majorversion'] = get_string('majorVersionRequired', 'qtype_algebrakit');
        }
        else if (!is_numeric($majorVersion) && $majorVersion !== 'latest') {
            $errors['majorVersionInvalid'] = get_string('majorVersionRequired', 'qtype_algebrakit');
        }
        return $errors;
    }

    public function qtype() {
        return 'algebrakit';
    }
}
