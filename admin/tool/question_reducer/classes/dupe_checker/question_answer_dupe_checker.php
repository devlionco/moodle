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
 * question_answer_dupe_checker class.
 *
 * Will check if question answers are duplicate
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\dupe_checker;
use tool_question_reducer\helper\comparer;

defined('MOODLE_INTERNAL') || die();

class question_answer_dupe_checker {

    private static $comparisonattributes = array(
        'answer',
        'answerformat',
        'fraction',
        'feedback',
        'feedbackformat'
    );

    public static function answers_are_duplicate($answera, $answerb) {
        return comparer::objects_are_duplicate($answera, $answerb, self::$comparisonattributes);
    }

    public static function question_answers_are_duplicate($answersa, $answersb) {
        return comparer::object_arrays_are_duplicate($answersa, $answersb, self::$comparisonattributes);
    }
}
