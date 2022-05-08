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

class absquestion_comment_link extends persistent
{
    const TABLE = 'absquestion_comment_link';

    protected $json;

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties()
    {
        return array(
            'assignid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'absqid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'absqcid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
        );
    }

    public static function get_assign_comments($assignid, $questionid = null, $isglobal = null) {
        $data = [];
        $params = [
            'assignid' => $assignid,
        ];
        if ($questionid) {
            $params['absqid'] = $questionid;
        }

        $links = static::get_records($params);
        foreach ($links as $link) {
            $commentparams = [
                'id' => $link->get('absqcid'),
            ];

            if (!is_null($isglobal)) {
                $commentparams['isglobal'] = $isglobal;
            }

            $comment = absquestion_comment::get_record($commentparams);

            if (!empty($comment)) {
                $data[] = (object) [
                    'id' => $link->get('id'),
                    'text' => $comment->get('text'),
                    'isglobal' => $comment->get('isglobal'),
                    'points' => $comment->get('grade'),
                    'qid' => $link->get('absqid')
                ];
            }
        }

        return $data;
    }

    public static function set_comments($data) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            if (isset($data['id']) && !empty($data['id'])) {
                if(!empty($link = static::get_record(['id' => $data['id']])) && !empty($comment = absquestion_comment::get_record(['id' => $link->get('absqcid')]))) {
                    $link->set('assignid', $data['assignid']);
                    $link->set('absqid', $data['qid']);
                    $link->update();
                    $comment->set('text', $data['text']);
                    $comment->set('grade', $data['points']);
                    $comment->set('method', $data['method']);
                    $comment->set('isglobal', $data['isglobal']);
                    $comment->update();
                }
            } else {
                $commentdata = (object) [
                    'text' => $data['text'],
                    'grade' => $data['points'],
                    'method' => $data['method'],
                    'isglobal' => $data['isglobal']
                ];

                $comment = new absquestion_comment(0, $commentdata);
                $comment->create();

                $linkdata = (object) [
                    'assignid' => $data['assignid'],
                    'absqid' => $data['qid'],
                    'absqcid' => $comment->get('id')
                ];

                $link = new static(0, $linkdata);
                $link->create();
            }

            $transaction->allow_commit();
            $result = true;
            $message = '';
        } catch (\Exception $e) {
            $transaction->dispose();
            $result = false;
            $message = $e->getMessage();
            $message .= $e->getFile();
            $message .= $e->getCode();
            $message .= $e->getLine();
            $message .= $e->getTraceAsString();
        }

        return ['result' => $result, 'message' => $message];
    }

    public static function delete_comments($id) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            if ($link = new static($id)) {
                $link->delete();
            }

            $transaction->allow_commit();
            $result = true;
            $message = '';
        } catch (\Exception $e) {
            $transaction->dispose();
            $result = false;
            $message = $e->getMessage();
            $message .= $e->getFile();
            $message .= $e->getCode();
            $message .= $e->getLine();
            $message .= $e->getTraceAsString();
        }

        return ['result' => $result, 'message' => $message];
    }
}