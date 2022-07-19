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
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html@package l GNU GPL v3 or later
 */

namespace gradingform_absquestion;

use core\persistent;

class absquestion_comment extends persistent {
    const TABLE = 'absquestion_comment';

    const COLORARRAY = [
        "",
        "#262949",
        "#700460",
        "#A02C5D",
        "#EC0F47",
        "#EE6B3B",
        "#FBBF54",
        "#ABD96D",
        "#15C286",
        "#087353",
        "#65aee7",
    ];

    protected $json;

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'text' => array(
                'type' => PARAM_RAW,
                'default' => null
            ),
            'grade' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'method' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'isglobal' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
        );
    }

    protected function after_delete($result) {
        $links = absquestion_comment_link::get_records(['absqcid' => $this->get('id')]);
        foreach ($links as $link) {
            $link->delete();
        }
    }

    public static function get_assign_comments_for_template($assignid, $gradeid) {
        global $DB;

        $return = [];

        $definition = absquestion::fetch_definition($assignid);

        $activeabs = absquestion::get_record(['definitionid' => $definition->id, 'validated' => 1]);
        if ($activeabs) {
            $grademethodstr = get_string('grading_method_' . $activeabs->get('method'), 'gradingform_absquestion');
            $questions = absquestion_question::get_records(['absid' => $activeabs->get('id'), 'parentid' => 0], 'sequence');

            $globalcomments = absquestion_comment_link::get_assign_comments($assignid, null, 1);

            foreach ($questions as $question) {

                $sequence = $question->get('sequence');
                $qid = $question->get('id');

                $usedpoints = $gradeid ? static::get_usedpoints($gradeid, $qid) : 0;

                $name = get_string('question', 'gradingform_absquestion') . ' ' . $sequence;

                $comments = absquestion_comment_link::get_assign_comments($assignid, $question->get('id'), 0);
                $commentsdata = [];

                foreach ($comments as $comment) {

                    $commenttext = $name .' ('. $comment->points . ')' . '<br/>' . $comment->text;

                    $commentsdata[] = (object)[
                        'text' => $commenttext,
                        'id' => $comment->id,
                        'points' => $comment->points,
                        'isglobal' => $comment->isglobal,
                    ];
                }

                foreach ($globalcomments as $globalcomment) {

                    $globalcommenttext = $name .' ('. $globalcomment->points . ')' . '<br/>' . $globalcomment->text;

                    $commentsdata[] = (object)[
                        'text' => $globalcommenttext,
                        'id' => $globalcomment->id,
                        'points' => $globalcomment->points,
                        'isglobal' => $globalcomment->isglobal,
                    ];
                }

                $subquestions = absquestion_question::get_records(
                    [
                        'absid' => $activeabs->get('id'),
                        'parentid' => $question->get('id')
                    ],
                    'sequence'
                );
                $subquestionsdata = [];

                foreach ($subquestions as $subquestion) {

                    $subsequence = $subquestion->get('sequence');
                    $subqid = $subquestion->get('id');

                    $subusedpoints = $gradeid ? static::get_usedpoints($gradeid, $subqid) : 0;
                    $usedpoints += $subusedpoints;

                    $subname = $name . '.' . $subsequence;

                    $subquestionscommentsdata = [];
                    $subcomments = absquestion_comment_link::get_assign_comments($assignid, $subqid, 0);

                    foreach ($subcomments as $subcomment) {
                        $subtext = $subname .' ('. $subcomment->points . ')' . '<br/>' . $subcomment->text;

                        $subquestionscommentsdata[] = [
                            'text' => $subtext,
                            'id' => $subcomment->id,
                            'points' => $subcomment->points,
                            'isglobal' => $subcomment->isglobal,
                        ];
                    }

                    foreach ($globalcomments as $globalcomment) {
                        $globalcommentsubtext = $subname .' ('. $globalcomment->points . ')' . '<br/>' . $globalcomment->text;

                        $subquestionscommentsdata[] = (object)[
                            'text' => $globalcommentsubtext,
                            'id' => $globalcomment->id,
                            'points' => $globalcomment->points,
                            'isglobal' => $globalcomment->isglobal
                        ];
                    }

                    $subquestionsdata[] = (object)[
                        'subqComments' => $subquestionscommentsdata,
                        'subqId' => $subqid,
                        'usedpoint' => $subusedpoints,
                        'max' => $subquestion->get('qmax'),
                        'color' => !empty($activeabs->get('questioncolor')) && isset(static::COLORARRAY[$sequence])
                                ? static::COLORARRAY[$sequence]
                                : static::COLORARRAY[0],
                        'sequence' => $subsequence,
                        'info' => $subquestion->get('info') ?? '',
                        'grademethod' => $grademethodstr,
                    ];
                }

                $return[] = (object)[
                    'comments' => $commentsdata,
                    'subq' => $subquestionsdata,
                    'sequence' => $sequence,
                    'color' => !empty($activeabs->get('questioncolor')) && isset(static::COLORARRAY[$sequence])
                        ? static::COLORARRAY[$sequence]
                        : static::COLORARRAY[0],
                    'max' => $question->get('qmax'),
                    'info' => $question->get('info') ?? '',
                    'questionId' => $qid,
                    'usedpoint' => $usedpoints,
                    'qorder' => $sequence,
                    'grademethod' => $grademethodstr,
                ];
            }
        }

        return $return;
    }

    public static function get_usedpoints($gradeid, $questionid) {
        global $DB;
        $usedpoints = 0;
        $draftedcount = $undraftedcount = [];
        $draftedcomments = $DB->get_records('assignfeedback_editpdf_absq',
            [
                'gradeid' => $gradeid,
                'questionid' => $questionid,
                'draft' => 1
            ]
        );
        $undraftedcomments = $DB->get_records('assignfeedback_editpdf_absq',
            [
                'gradeid' => $gradeid,
                'questionid' => $questionid,
                'draft' => 0
            ]
        );

        foreach ($undraftedcomments as $undraftedcomment) {
            $key = $questionid . '_' . $undraftedcomment->commentid;
            if (!isset($undraftedcount[$key])) {
                $undraftedcount[$key] = 0;
            }
            $undraftedcount[$key]++;
            $usedpoints += $undraftedcomment->points;
        }

        foreach ($draftedcomments as $draftedcomment) {
            $key = $questionid . '_' . $draftedcomment->commentid;
            if (!isset($draftedcount[$key])) {
                $draftedcount[$key] = 0;
            }
            $draftedcount[$key]++;
            if ($draftedcount[$key] > $undraftedcount[$key]) {
                $usedpoints += $draftedcomment->points;
            }
        }

        return $usedpoints;
    }
}
