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
 * question_merger class.
 *
 * Will merge duplicate questions
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\dupe_merger;

defined('MOODLE_INTERNAL') || die();

class question_merger {

    public static function merge_questions($questions, $qtype) {
        global $DB;

        // Dupe questions will be merged into master.
        $master = reset($questions);

        $duplicates = array_slice($questions, 1);

        try {
            $transaction = $DB->start_delegated_transaction();
            foreach ($duplicates as $duplicate) {
                self::merge_question($master, $duplicate);
            }
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
        }
    }

    private static function merge_question($master, $duplicate) {
        self::merge_quiz_slots($master, $duplicate);
        self::merge_question_attempts($master, $duplicate);
        self::delete_question($duplicate);
    }

    private static function merge_quiz_slots($master, $duplicate) {
        global $DB;
        $sql = "UPDATE {quiz_slots}
                SET questionid = :masterid
                WHERE questionid = :duplicateid";
        $params = array(
            'masterid' => $master->id,
            'duplicateid' => $duplicate->id,
        );
        $DB->execute($sql, $params);
    }

    private static function merge_question_attempts($master, $duplicate) {
        global $DB;
        $sql = "UPDATE {question_attempts}
                SET questionid = :masterid
                WHERE questionid = :duplicateid";
        $params = array(
            'masterid' => $master->id,
            'duplicateid' => $duplicate->id,
        );
        $DB->execute($sql, $params);
    }

    private static function delete_question($duplicate) {
        if (questions_in_use(array($duplicate->id))) {
            throw new \Exception("question {$duplicate->id} is in use. Cannot delete.");
        }

        question_delete_question($duplicate->id);
    }
}