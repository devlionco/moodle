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
 * qcat_dupe_question_merger class.
 *
 * Will merge dupelicate questions within supplied question category.
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\dupe_merger;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

class category_dupe_question_merger {

    private static $questioncache = [];

    public static function purge_question_cache() {
        self::$questioncache = [];
    }

    public static function get_questions($qcat, $qtype) {
        if (isset(self::$questioncache[$qcat->id])) {
            if (isset(self::$questioncache[$qcat->id][$qtype])) {
                return self::$questioncache[$qcat->id][$qtype];
            }
        }
        global $DB;
        $sql = "SELECT * from {question} q
                WHERE q.category = :qcatid
                    AND q.qtype = :qtype
                    AND q.parent = 0";

        $params = array(
            'qcatid'        => $qcat->id,
            'qtype'         => $qtype,
        );

        $questions = $DB->get_records_sql($sql, $params);
        if (!isset(self::$questioncache[$qcat->id])) {
            self::$questioncache[$qcat->id] = [];
        }
        self::$questioncache[$qcat->id][$qtype] = $questions;
        return $questions;
    }

    public static function merge_question_into_parent($parentquestion, $question, $parentcategory) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            self::merge_question($parentquestion, $question, $parentcategory);
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
        }
    }

    public static function try_delete_category($category) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            self::delete_dupe_category($category);
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
        }
    }

    public static function try_set_question_parent_category($parentcategory, $question) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            self::set_questions_category($parentcategory, $question);
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
        }
    }

    private static function merge_question($parentquestion, $question, $parentcategory) {
        self::merge_quiz_slots($parentquestion, $question);
        self::merge_question_attempts($parentquestion, $question);
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

    public static function set_questions_category($parentcategory, $question) {
        global $DB;
        $sql = "UPDATE {question}
                SET category = :categoryid
                WHERE id = :questionid";
        $params = array(
            'categoryid' => $parentcategory->id,
            'questionid' => $question->id,
        );
        $DB->execute($sql, $params);
    }

    public static function delete_dupe_category($category) {
        global $DB;
        $sql = "DELETE FROM {question_categories}
                WHERE id = :categoryid";
        $params = array(
            'categoryid' => $category->id,
        );
        $DB->execute($sql, $params);
    }

    public static function delete_question($duplicate) {
        if (questions_in_use(array($duplicate->id))) {
            return false;
        }

        question_delete_question($duplicate->id);
        return true;
    }
}
