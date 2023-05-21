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
use tool_question_reducer\dupe_checker\question_dupe_checker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

class qcat_dupe_question_merger {

    public static function merge_duplicates($qcat) {
        $supportedquestiontypes = question_dupe_checker::get_supported_question_types();
        foreach ($supportedquestiontypes as $qtype) {
            $success = self::merge_qtype_duplicates($qcat, $qtype);
            while ($success) {
                $success = self::merge_qtype_duplicates($qcat, $qtype);
            }
        }
    }

    /**
     * Merges duplicates for the qtype within a qcat.
     *
     * @param \stdClass $qcat
     * @param string $qtype
     * @return bool success
     */
    private static function merge_qtype_duplicates($qcat, $qtype) {
        $duplicatesfound = false;

        // Subgroup_based_on_question isnt optimal so this will keep going to get around that.
        do {
            // Get all questions with same name.
            $samenamegroups = self::get_question_groups_with_same_name($qcat, $qtype);

            // Group now based on all question data.
            $identicalquestiongroups = array();
            foreach ($samenamegroups as $group) {
                $identicalquestiongroups = $identicalquestiongroups + self::subgroup_based_on_question($group, $qtype);
            }

            foreach ($identicalquestiongroups as $group) {
                question_merger::merge_questions($group, $qtype);
            }

            if (!empty($identicalquestiongroups)) {
                $duplicatesfound = true;
            }
        } while (!empty($identicalquestiongroups));

        return $duplicatesfound;
    }

    private static function get_question_groups_with_same_name($qcat, $qtype) {
        global $DB;
        $sql = "SELECT * from {question} q
                WHERE q.category = :qcatid
                    AND q.qtype = :qtype
                    AND q.parent = 0
                    AND q.name in ( SELECT q.name
                                    FROM {question} q
                                    INNER JOIN {question_categories} qc on qc.id = q.category
                                    WHERE q.category = :qcatidtwo
                                        AND q.qtype = :qtypetwo
                                    GROUP BY q.name
                                    HAVING count(q.name) > 1)";

        $params = array(
            'qcatid'        => $qcat->id,
            'qcatidtwo'     => $qcat->id,
            'qtype'         => $qtype,
            'qtypetwo'      => $qtype,
        );

        $questions = $DB->get_records_sql($sql, $params);

        // Attach question type data.
        get_question_options($questions);

        // Group by name.
        $groupedquestions = array();
        foreach ($questions as $question) {
            if (!isset($groupedquestions[$question->name])) {
                $groupedquestions[$question->name] = array();
            }
            $groupedquestions[$question->name][] = $question;
        }

        return $groupedquestions;
    }

    private static function subgroup_based_on_question($questions, $qtype) {
        $subgroups = array();

        foreach ($questions as $question) {
            $questioningroup = false;
            foreach ($subgroups as $key => $group) {
                // We cannot merge questions that share a quiz, such that two quiz slots have the same question id as
                // this will lead to issues. There is definately a better way to do this, will not figure out optimal groupings.
                if (self::question_shares_quiz_with_any_questions($question, $group)) {
                    continue;
                }

                $groupquestion = reset($group); // Only need to check against first.
                if (question_dupe_checker::questions_are_duplicate($question, $groupquestion, $qtype)) {
                    $subgroups[$key][$question->id] = $question;
                    $questioningroup = true;
                    break;
                }
            }

            if (!$questioningroup) {
                $subgroups[] = array($question->id => $question);
            }
        }

        foreach ($subgroups as $key => $subgroup) {
            if (count($subgroup) < 2) {
                unset($subgroups[$key]);
            }
        }

        return $subgroups;
    }

    private static function question_shares_quiz_with_any_questions($question, $questions) {
        global $DB;

        list($insql, $params) = $DB->get_in_or_equal(array_keys($questions), SQL_PARAMS_NAMED);
        $params['questionid'] = $question->id;

        // INTERSECT not supported by mysql.
        $sql = "(
                    SELECT distinct(qsa.quizid)
                    FROM {quiz_slots} qsa
                    WHERE qsa.questionid {$insql}
                )
                INTERSECT
                (
                    SELECT distinct(qsb.quizid)
                    FROM {quiz_slots} qsb
                    WHERE qsb.questionid = :questionid
                )";

        return $DB->record_exists_sql($sql, $params);
    }
}
