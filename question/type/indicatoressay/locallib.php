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
 * Plugin general functions are defined here.
 *
 * @package     qtype_indicatoressay
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function qtype_indicatoressay_get_indicators() {
    global $DB;

    $indicators = [];

    $indicators = $DB->get_records("qtype_indicatoressay_ind", ['deleted' => 0]);

    return $indicators;
}

function qtype_indicatoressay_update_indicators($indicators) {
    global $DB;

    $existingindicators = qtype_indicatoressay_get_indicators();

    foreach ($indicators as $key => $indicator) {
        if (isset($indicator['id']) && $DB->get_record('qtype_indicatoressay_ind', ['id' => $indicator['id']])) {
            $DB->update_record('qtype_indicatoressay_ind', $indicator);
        } elseif (isset($indicator['indicatorid'])) {
            if ($samename = $DB->get_record_sql('SELECT *
            FROM {qtype_indicatoressay_ind}
            WHERE `indicatorid` = ?
            ', [$indicator['indicatorid']], IGNORE_MISSING)) {
                $indicator['model'] = !isset($indicator['model']) ? '' : $indicator['model'];
                $indicator['id'] = $samename->id;
                $indicators[$key]['id'] = $samename->id;
                $DB->update_record('qtype_indicatoressay_ind', $indicator);
            } else {
                $indicator['model'] = !isset($indicator['model']) ? '' : $indicator['model'];
                $indicators[$key]['id'] = $DB->insert_record('qtype_indicatoressay_ind', $indicator);
            }
        }
    }

    foreach ($existingindicators as $existingindicator) {
        $found = false;
        foreach ($indicators as $indicator) {
            if ($existingindicator->id == $indicator['id']) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $DB->delete_records('qtype_indicatoressay_ind', ['id' => $existingindicator->id]);
        }
    }

    return true;
}

function qtype_indicatoressay_get_available_indicators() {
    global $DB;

    $indicators = [];

    $indicators = $DB->get_records("qtype_indicatoressay_ind", ['visible' => 1, 'deleted' => 0]);

    return $indicators;
}

function qtype_indicatoressay_get_question_indicators($qid = null) {
    global $DB;

    if (is_null($qid)) {
        return [1, []];
    }

    $indicators = [];

    $existqinds = $DB->get_field('qtype_indicatoressay_options', 'indicators', array('questionid' => $qid));

    $existqinds = qtype_indicatoressay_parse_ind_options_json($existqinds);

    $indicators = $existqinds->indicatorlist;

    // Get isgradestypescalar, check and set default if needed.
    if (!isset($existqinds->isgradestypescalar)) {
        $existqinds->isgradestypescalar = 1;
    }
    // Get researchquestion, check and set default if needed.
    if (!isset($existqinds->researchquestion)) {
        $existqinds->researchquestion = 0;
    }

    return [$existqinds->isgradestypescalar, $indicators, $existqinds->researchquestion];
}

function qtype_indicatoressay_prepare_ind_options_json($isgradestypescalar, $questionindicatorfulltable, $researchquestion = false) {
    $indicatorsoptions = new stdClass();
    $indicatorsoptions->isgradestypescalar = $isgradestypescalar;
    $indicatorsoptions->researchquestion = $researchquestion;
    $indicatorsoptions->indicatorlist = json_decode($questionindicatorfulltable);

    $indicatorsoptions = json_encode($indicatorsoptions);

    return $indicatorsoptions;
}

function qtype_indicatoressay_parse_ind_options_json($json) {
    $indicatorsoptions = json_decode($json);

    return $indicatorsoptions;
}

function qtype_indicatoressay_store_grades($data, $qaid) {
    global $DB;

    $result = false;

    $qa = $DB->get_record('question_attempts', ['id' => $qaid]);
    $time = time();

    foreach ($data->indicatorlist as $key => $indicator) {

        $indicator->qindid = $indicator->id;

        $responsedata = [
            'questionattemptid' => $qaid,
            'questionid' => $qa->questionid,
            'quizattemptid' => 0,
            'question' => $qa->questionsummary,
            'answer' => $qa->responsesummary,
            'timecreated' => $time,
            'timemodified' => $time,
            'isgradestypescalar' => $data->isgradestypescalar,
            'weight' => $indicator->weight,
            'indicatorid' => $indicator->indicatorid,
            'name' => $indicator->name,
            'type' => $indicator->type,
            'qindid' => $indicator->qindid,
            'grade' => strval($data->grade),
            'checked' => $indicator->checked,
            'normalizedweight' => $indicator->normalizedWeight,
            'weightedgrade' => $indicator->weightedGrade,
            'maxmark' => $data->maxmark,
            'minfraction' => $data->minfraction,
            'maxfraction' => $data->maxfraction,
            'usageid' => $data->usageid,
            'slot' => $data->slot,
        ];

        $resp = $DB->get_record('qtype_indicatoressay_resp', ['questionattemptid' => $qaid, 'qindid' => $indicator->qindid]);
        if ($resp) {
            $responsedata['id'] = $resp->id;
            $result = $DB->update_record('qtype_indicatoressay_resp', $responsedata);
        } else {
            $result = $DB->insert_record('qtype_indicatoressay_resp', $responsedata, false);
        }

    }

    return $result;
}

function qtype_indicatoressay_get_usedindicators() {
    global $DB;

    $sql = "SELECT DISTINCT
                qv.questionid
            FROM
                {question_versions} qv
            JOIN {question_references} qr ON qv.questionbankentryid = qr.questionbankentryid
            JOIN {quiz_slots} qs ON qr.itemid = qs.id
            JOIN {question} q ON q.id = qv.questionid
            WHERE
                q.qtype = 'indicatoressay'
                AND(qr.version IS NULL
                AND qv.version = (
                    SELECT
                        MAX(version)
                        FROM {question_versions} qv2
                    WHERE
                        qv2.questionbankentryid = qr.questionbankentryid)
                    OR qr.version IS NOT NULL
                    AND qv.version = qr.version)";

    $questionids = $DB->get_records_sql($sql);
    $usedindicators = [];

    foreach ($questionids as $questionid) {
        $options = $DB->get_record('qtype_indicatoressay_options', ['questionid' => $questionid->questionid]);

        if ($options && !empty($options->indicators)) {
            $indicators = json_decode($options->indicators);

            if (isset($indicators->indicatorlist) && is_array($indicators->indicatorlist)) {
                foreach ($indicators->indicatorlist as $indicator) {
                    if ($indicator && isset($indicator->indicatorid)) {
                        $usedindicators[] = $indicator->indicatorid;
                    }
                }
            }
        }
    }

    return $usedindicators;
}

function qtype_indicatoressay_is_student() {
    global $USER, $COURSE;

    $result = false;
    $context = context_course::instance($COURSE->id);
    $capability = 'moodle/course:update';
    if (!has_capability($capability, $context, $USER->id)) {
        $result = true;
    }

    return $result;
}
