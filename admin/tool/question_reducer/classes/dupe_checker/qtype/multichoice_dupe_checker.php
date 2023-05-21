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
 * multichoice_dupe_checker class.
 *
 * Will check if multichoice questions are duplicate
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\dupe_checker\qtype;

defined('MOODLE_INTERNAL') || die();

class multichoice_dupe_checker extends qtype_dupe_checker {
    protected static function get_qtype_option_fields() {
        return array(
            'layout',
            'single',
            'shuffleanswers',
            'correctfeedback',
            'partiallycorrectfeedback',
            'incorrectfeedback',
            'answernumbering',
            'correctfeedbackformat',
            'partiallycorrectfeedbackformat',
            'incorrectfeedbackformat',
            'shownumcorrect',
        );
    }

    public static function questions_have_answers() {
        return true;
    }

    public static function questions_have_hints() {
        return true;
    }
}