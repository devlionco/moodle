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
 * context_question_reducer class.
 *
 * Will reduce questions within supplied context.
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer;
use tool_question_reducer\dupe_merger\qcat_dupe_question_merger;

defined('MOODLE_INTERNAL') || die();

class context_question_reducer {

    public static function reduce_questions($contextid) {

        // Get all question categories in context.
        $qcats = self::get_all_question_categories($contextid);

        // Merge duplicate question categories with context
        // \\tool_question_reducer\\dupe_qcat_merger something something.

        // Merge duplicate questions within question categories.
        self::merge_duplicate_questions_within_question_categories($qcats);
    }

    private static function merge_duplicate_questions_within_question_categories($qcats) {
        foreach ($qcats as $qcat) {
            qcat_dupe_question_merger::merge_duplicates($qcat);
        }
    }

    private static function get_all_question_categories($contextid) {
        global $DB;
        $qcats = $DB->get_records('question_categories', array('contextid' => $contextid));
        return $qcats;
    }
}
