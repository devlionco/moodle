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

use tool_question_reducer\dupe_merger\category_dupe_question_merger;
use tool_question_reducer\dupe_merger\question_merger;
use tool_question_reducer\dupe_checker\question_dupe_checker;

defined('MOODLE_INTERNAL') || die();

class context_category_reducer {

    public static function reduce_categories($contextid) {
        self::remove_empty_categories($contextid);

        self::merge_duplicate_questions_within_question_categories($contextid);
    }

    private static function remove_empty_categories($contextid) {
        $emptycategories = true;
        while ($emptycategories) {
            $qcats = self::get_all_categories($contextid);
            $iscatempty = false;
            foreach ($qcats as $categorya) {
                $haschild = false;
                foreach ($qcats as $categoryb) {
                    if ($categoryb->parent == $categorya->id) {
                        $haschild = true;
                    }
                }
                if (!$haschild) {
                    $catquestions = self::get_all_questions_for_cat($categorya);
                    if (count($catquestions) == 0) {
                        echo ("Category $categorya->name is empty and has no children, deleting...\n");
                        category_dupe_question_merger::purge_question_cache();
                        category_dupe_question_merger::try_delete_category($categorya);
                        $iscatempty = true;
                        break;
                    }
                }
            }
            if (!$iscatempty) {
                $emptycategories = false;
                echo ("Empty categories removed...\n");
            }
        }
    }

    private static function merge_duplicate_questions_within_question_categories($contextid) {
        $hasduplicates = true;
        $currentcat = 0;

        while ($hasduplicates) {
            $hasmatch = false;
            $qcats = self::get_all_categories($contextid);
            $supportedquestiontypes = question_dupe_checker::get_supported_question_types();

            $values = array_values($qcats);
            $count = count($values);

            echo("Checking through $count categories \n");

            echo($currentcat . "\n");

            for ($i = $currentcat; $i < $count - 1; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {

                    $totalmatches = 0;
                    $totalcataquestions = $totalcatbquestions = $totalmatchedbquestions = array();

                    foreach ($supportedquestiontypes as $qtype) {

                        $cata = $values[$i];
                        $catb = $values[$j];

                        $cataquestions = array_values(category_dupe_question_merger::get_questions($values[$i], $qtype));
                        $catbquestions = array_values(category_dupe_question_merger::get_questions($values[$j], $qtype));

                        $totalcataquestions = array_merge($totalcataquestions, $cataquestions);
                        $totalcatbquestions = array_merge($totalcatbquestions, $catbquestions);

                        $returndata = self::compare_questions($cataquestions, $catbquestions, $qtype);

                        $matchedbquestion = array_values($returndata->matchedquestions);
                        $totalmatchedbquestions = array_merge($totalmatchedbquestions, $matchedbquestion);

                        $totalmatches = $totalmatches + $returndata->crosscatmatches;
                    }

                    $totalcatbqcount = count($totalcatbquestions);

                    if ($totalmatches > 10) {
                        echo ("There were $totalmatches out of " . $totalcatbqcount . " between $cata->name and $catb->name \n");
                    }

                    if ($totalmatches > ($totalcatbqcount / 2)) {
                        echo("\n");
                        echo("There were $totalmatches out of " . $totalcatbqcount . " between $cata->name and $catb->name \n");
                        echo ("Merging $catb->name questions into $cata->name \n");
                        $msg = self::merge_category_questions($totalmatchedbquestions, $totalcatbquestions, $cata);
                        if ($msg) {
                            category_dupe_question_merger::purge_question_cache();
                            echo ("Category $catb->name has been merged... Restarting test \n\n");
                            $hasmatch = true;
                            $currentcat = $i;
                            break 2;
                        }
                    }
                }
                echo("Category $i: " . $values[$i]->name . " passed check \n\n");
            }
            if (!$hasmatch) {
                $hasduplicates = false;
            }
        }
        echo ("Finished checking categories\nCleaning up empty categories\n");
        self::remove_empty_categories($contextid);
    }

    private static function compare_questions($cataquestions, $catbquestions, $qtype) {
        $returndata = new \stdClass();
        $crosscatmatches = 0;
        $questionmatches = $matchedquestions = array();
        for ($x = 0; $x < count($cataquestions); $x++) {
            for ($y = 0; $y < count($catbquestions); $y++) {
                if (question_dupe_checker::questions_are_duplicate($cataquestions[$x], $catbquestions[$y], $qtype)) {
                    $matchedquestions[$crosscatmatches] = $catbquestions[$y];
                    $crosscatmatches++;
                }
            }
        }
        $returndata->matchedquestions = $matchedquestions;
        $returndata->crosscatmatches = $crosscatmatches;
        return $returndata;
    }

    private static function merge_category_questions($totalmatchedbquestions, $totalcatbquestions, $parentcat) {
        $deletesuccess = true;

        foreach ($totalcatbquestions as $catbquestion) {
            $match = false;
            foreach ($totalmatchedbquestions as $matchedbquestion) {
                if ($matchedbquestion->id == $catbquestion->id) {
                    $match = true;
                    category_dupe_question_merger::merge_question_into_parent($catbquestion, $matchedbquestion, $parentcat);
                    $deletesuccess = self::delete_question_from_child($matchedbquestion);
                    if (!$deletesuccess) {
                        return $deletesuccess;
                    }
                }
            }
            if (!$match) {
                echo ("Moving question " . $catbquestion->name . " into " . $parentcat->name . "\n");
                category_dupe_question_merger::try_set_question_parent_category($parentcat, $catbquestion);
            }
        }
        return $deletesuccess;
    }

    private static function delete_question_from_child($matchedquestion) {
        $deletesuccess = true;
        $deletesuccess = category_dupe_question_merger::delete_question($matchedquestion);
        if ($deletesuccess == false) {
            echo ("Questions in use... skipping\n");
            return $deletesuccess;
        } else {
            echo ("Removing duplicate question " . $matchedquestion->name . "\n");
            return $deletesuccess;
        }
    }

    public static function get_all_questions_for_cat($category) {
        $totalcatquestions = array();
        $allquestiontypes = array("vet_upload", "calculatedmulti", "random", "match",
                                "calculated", "ddmarker",
                                "pmatch", "shortanswer", "truefalse",
                                "description", "ddwtos", "essay", "calculatedsimple",
                                "numerical", "ddimageortext", "gapselect", "randomsamatch",
                                "multichoice", "multianswer");

        foreach ($allquestiontypes as $qtype) {

            $catquestions = array_values(category_dupe_question_merger::get_questions($category, $qtype));

            $totalcatquestions = array_merge($totalcatquestions, $catquestions);
        }
        return $totalcatquestions;
    }

    private static function get_all_categories($contextid) {
        global $DB;
        $qcats = $DB->get_records('question_categories', array('contextid' => $contextid));
        return $qcats;
    }
}
