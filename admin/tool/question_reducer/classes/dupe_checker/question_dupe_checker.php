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
 * question_dupe_checker class.
 *
 * Will check if questions are duplicate
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\dupe_checker;
use tool_question_reducer\helper\comparer;

defined('MOODLE_INTERNAL') || die();

class question_dupe_checker {

    public static function get_supported_question_types() {
        global $CFG;
        $path = $CFG->dirroot . '/admin/tool/question_reducer/classes/dupe_checker/qtype/*_dupe_checker.php';
        $qtypefilenames = glob($path);

        $supportedqtypes = array();
        foreach ($qtypefilenames as $filename) {
            $supportedqtype = basename(str_replace('_dupe_checker.php', '', $filename));

            // Ignore the abstract class.
            if ($supportedqtype === 'qtype') {
                continue;
            }
            $supportedqtypes[] = $supportedqtype;
        }

        return $supportedqtypes;
    }

    public static function questions_are_duplicate($questiona, $questionb, $qtype) {
        if (!self::base_questions_are_duplicate($questiona, $questionb)) {
            return false;
        }

        $questiontypedupechecker = "\\tool_question_reducer\\dupe_checker\\qtype\\{$qtype}_dupe_checker";

        if ($questiontypedupechecker::questions_have_answers()) {
            $answersa = $questiona->options->answers;
            $answersb = $questionb->options->answers;
            if (!question_answer_dupe_checker::question_answers_are_duplicate($answersa, $answersb)) {
                return false;
            }
        }

        if ($questiontypedupechecker::questions_have_hints()) {
            $hintsa = $questiona->hints;
            $hintsb = $questionb->hints;
            if (!question_hint_dupe_checker::question_hints_are_duplicate($hintsa, $hintsb)) {
                return false;
            }
        }

        if (!$questiontypedupechecker::questions_are_duplicate($questiona, $questionb)) {
            return false;
        }

        return true;
    }

    private static function base_questions_are_duplicate($questiona, $questionb) {
        $basecomparisonattributes = array(
            'questiontext',
            'questiontextformat',
            'generalfeedback',
            'generalfeedbackformat',
            'defaultmark',
            'penalty',
            'length',
            'hidden'
        );
        return comparer::objects_are_duplicate($questiona, $questionb, $basecomparisonattributes);
    }
}