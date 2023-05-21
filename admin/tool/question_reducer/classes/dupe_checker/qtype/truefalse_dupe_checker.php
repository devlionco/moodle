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
use tool_question_reducer\dupe_checker\question_answer_dupe_checker;

defined('MOODLE_INTERNAL') || die();

class truefalse_dupe_checker extends qtype_dupe_checker {

    protected static function specific_qtype_details_are_duplicate($questiona, $questionb) {
        // Need to check that the answers identified by options->trueanswer
        // and options->falseanser are equal.

        $trueanswera = $questiona->options->answers[$questiona->options->trueanswer];
        $trueanswerb = $questionb->options->answers[$questionb->options->trueanswer];

        if (!question_answer_dupe_checker::answers_are_duplicate($trueanswera, $trueanswerb)) {
            return false;
        }

        $falseanswera = $questiona->options->answers[$questiona->options->falseanswer];
        $falseanswerb = $questionb->options->answers[$questionb->options->falseanswer];

        if (!question_answer_dupe_checker::answers_are_duplicate($falseanswera, $falseanswerb)) {
            return false;
        }

        return true;
    }

    public static function questions_have_answers() {
        return true;
    }
}