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

class absquestion_group extends persistent
{
    const TABLE = 'absquestion_group';

    protected $json;

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties()
    {
        return array(
            'absid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'sequence' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'name' => array(
                'type' => PARAM_TEXT,
                'default' => NULL
            ),
            'grouppass' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
        );
    }

    public static function save_data($data) {
        $groups = static::get_records(['absid' => $data['id']]);
        $grouppass = [];
        foreach ($data['grouppass'] as $key => $value) {
            $grouppass[$value['sequence']] = $value;
        }

        $groupidsbysequence = [];

        foreach ($data['groups'] as $groupvalue) {
            $sequence = $groupvalue['sequence'];
            $groupdata = (object) [
                'absid' => $data['id'],
                'name' => $groupvalue['value'],
                'sequence' => $sequence,
                'grouppass' => $grouppass[$sequence]['value'] ?? 0
            ];

            if ($groups[$groupvalue['id']]) {
                $group = $groups[$groupvalue['id']];
                $group->from_record($groupdata);
                $group->update();
                unset($groups[$groupvalue['id']]);
            } else {
                $group = new self(0, $groupdata);
                $group->create();
            }
            $groupidsbysequence[$sequence] = $group->get('id');
        }

        absquestion_question::save_data($data, $groupidsbysequence);

        foreach ($groups as $group) {
            $group->delete();
        }

        return $groupidsbysequence;
    }

    public function after_delete($result)
    {
        $questions = absquestion_question::get_records(['absgid' => $this->get('id')]);
        foreach ($questions as $question) {
            $question->delete();
        }
    }

    public static function fetch_data($absid) {
        $data = [
            'groups' => [],
            'grouppass' => [],
        ];

        $groups = static::get_records(['absid' => $absid], 'sequence');
        $groupsbyid = [];
        foreach ($groups as $group) {
            $data['groups'][] = [
                'id' => $group->get('id'),
                'value' => $group->get('name'),
                'sequence' => $group->get('sequence'),
            ];

            $data['grouppass'][] = [
                'value' => $group->get('grouppass'),
                'sequence' => $group->get('sequence'),
            ];

            $groupsbyid[$group->get('id')] = $group;
        }

        $data['questions'] = absquestion_question::fetch_data($absid, $groupsbyid);

        return $data;
    }
}