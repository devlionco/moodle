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

class absquestion_question extends persistent {
    const TABLE = 'absquestion_question';

    protected $json;

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
                'absid' => array(
                        'type' => PARAM_INT,
                        'default' => 0
                ),
                'absgid' => array(
                        'type' => PARAM_INT,
                        'default' => 0
                ),
                'sequence' => array(
                        'type' => PARAM_INT,
                        'default' => 0
                ),
                'parentid' => array(
                        'type' => PARAM_INT,
                        'default' => 0
                ),
                'qmax' => array(
                        'type' => PARAM_INT,
                        'default' => 0
                ),
                'bonus' => array(
                        'type' => PARAM_INT,
                        'default' => 0
                ),
                'info' => array(
                        'type' => PARAM_RAW,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ),
        );
    }

    public static function save_data($data, $groupidsbysequence) {
        //we should use get_records_select since get_records come unkeyed
        $questions =
                static::get_records_select('parentid = :parentid AND absid = :absid', ['parentid' => 0, 'absid' => $data['id']]);

        foreach ($data['questions'] as $questionvalue) {
            $questiondata = (object) [
                    'absid' => $data['id'],
                    'absgid' => !empty($questionvalue['group']) ? $groupidsbysequence[$questionvalue['group']] : 0,
                    'sequence' => $questionvalue['sequence'],
                    'qmax' => $questionvalue['qmax'],
                    'bonus' => $questionvalue['bonus'],
                    'parentid' => 0
            ];

            if ($questions[$questionvalue['id']]) {
                $question = $questions[$questionvalue['id']];
                $question->from_record($questiondata);
                $question->update();
                unset($questions[$questionvalue['id']]);
            } else {
                $questiondata->info = null;
                $question = new self(0, $questiondata);
                $question->create();
            }

            if (isset($questionvalue['subq']) && is_array($questionvalue['subq'])) {
                $subquestions = static::get_records_select('parentid = :parentid AND absid = :absid',
                        ['parentid' => $question->get('id'), 'absid' => $data['id']]);

                foreach ($questionvalue['subq'] as $subquestionvalue) {
                    $subquestiondata = (object) [
                            'absid' => $data['id'],
                            'absgid' => 0,
                            'sequence' => $subquestionvalue['sequence'],
                            'qmax' => $subquestionvalue['qmax'],
                            'bonus' => 0,
                            'parentid' => $question->get('id')
                    ];

                    if ($subquestions[$subquestionvalue['id']]) {
                        $subquestion = $subquestions[$subquestionvalue['id']];
                        $subquestion->from_record($subquestiondata);
                        $subquestion->update();
                        unset($subquestions[$subquestionvalue['id']]);
                    } else {
                        $subquestiondata->info = null;
                        $subquestion = new self(0, $subquestiondata);
                        $subquestion->create();
                    }
                }

                foreach ($subquestions as $todeletesubquestion) {
                    $todeletesubquestion->delete();
                }
            }
        }

        foreach ($questions as $todeletequestion) {
            $todeletequestion->delete();
        }
    }

    public function after_delete($result) {
        $subquestions = static::get_records(['parentid' => $this->get('id')]);
        foreach ($subquestions as $subquestion) {
            $subquestion->delete();
        }
    }

    public static function fetch_data($absid, $groups) {
        $data = [];

        $questions = static::get_records(['parentid' => 0, 'absid' => $absid], 'sequence');
        foreach ($questions as $question) {
            $questiondata = [
                    'id' => $question->get('id'),
                    'sequence' => $question->get('sequence'),
                    'group' => isset($groups[$question->get('absgid')]) ? $groups[$question->get('absgid')]->get('sequence') : 0,
                    'qmax' => $question->get('qmax'),
                    'bonus' => $question->get('bonus'),
                    'info' => $question->get('info'),
                    'parentid' => 0
            ];

            if ($subquestions = static::get_records(['parentid' => $question->get('id'), 'absid' => $absid], 'sequence')) {
                $questiondata['subq'] = [];
                foreach ($subquestions as $subquestion) {
                    $questiondata['subq'][] = [
                            'id' => $subquestion->get('id'),
                            'sequence' => $subquestion->get('sequence'),
                            'qmax' => $subquestion->get('qmax'),
                            'info' => $subquestion->get('info'),
                    ];
                }
            }

            $data[] = $questiondata;
        }

        return $data;
    }

    public static function save_info($id, $info) {
        $status = true;
        $message = '';
        if ($question = static::get_record(['id' => $id])) {
            $question->set('info', $info);
            $question->update();
        } else {
            $status = false;
            $message = get_string('noquestion', 'gradingform_absquestion');
        }

        return [$status, $message];
    }
}