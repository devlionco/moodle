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

class absquestion extends persistent {
    const TABLE = 'absquestion';

    const QTOTALMAXDEFAULT = 10;
    const SUBQNUMMAXDEFAULT = 10;

    protected $data;

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'definitionid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'qtotal' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'groupnum' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'method' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'totalqbonus' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'totalmaxgrade' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'validated' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'questioncolor' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'qtotalmax' => array(
                'type' => PARAM_INT,
                'default' => 10
            ),
            'subqnummax' => array(
                'type' => PARAM_INT,
                'default' => 10
            ),
        );
    }

    public function save_data($data) {
        $controller = static::fetch_controller($data['assignid']);
        if ($definition = $controller->get_definition(true)) {
            $absquestiondata = [
                'definitionid' => $definition->id,
                'qtotal' => $data['qtotal'],
                'groupnum' => isset($data['groups']) ? count($data['groups']) : 0,
                'method' => $data['method'],
                'totalqbonus' => $data['totalqbonus'],
                'totalmaxgrade' => $data['totalmaxgrade'],
                'validated' => $data['validated'],
            ];

            $this->from_record((object) $absquestiondata);

            if ($this->get('id')) {
                $this->update();
            } else if (!static::get_record(['definitionid' => $definition->id])) {
                $this->set('questioncolor', 0);
                $this->set('qtotalmax', static::QTOTALMAXDEFAULT);
                $this->set('subqnummax', static::SUBQNUMMAXDEFAULT);
                $this->create();
                $data['id'] = $this->get('id');
            } else {
                return;
            }

            $groupidsbysequence = [];
            if (isset($data['groups'])) {
                $groupidsbysequence = absquestion_group::save_data($data);
            }

            if (isset($data['questions'])) {
                absquestion_question::save_data($data, $groupidsbysequence);
            }

            $definition->status = $data['validated'] ?
                \gradingform_absquestion_controller::DEFINITION_STATUS_READY
                : \gradingform_absquestion_controller::DEFINITION_STATUS_DRAFT;

            $controller->update_definition($definition);
        }
    }

    public static function fetch_definition($assignid) {
        $return = null;
        if ($controller = static::fetch_controller($assignid)) {
            $definition = $controller->get_definition(true);
            if (!$definition) {
                $controller->update_definition(new \stdClass());
                $definition = $controller->get_definition(true);
            }
            $return = $definition;
        }

        return $return;
    }

    public static function fetch_controller($assignid) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/grade/grading/lib.php');
        require_once($CFG->dirroot . '/grade/grading/form/absquestion/lib.php');
        $return = null;

        $cm = get_coursemodule_from_instance('assign', $assignid);
        $context = \context_module::instance($cm->id);
        $gradingmanager = \get_grading_manager($context, 'mod_assign', 'submissions');
        $method = $gradingmanager->get_active_method();
        if ($method == \gradingform_absquestion_controller::ABSQUESTION) {
            $return = $gradingmanager->get_controller($method);
        }

        return $return;
    }

    public static function fetch_data($assignid) {
        $data = [];
        $definition = static::fetch_definition($assignid);
        $absquestion = static::get_record(['definitionid' => $definition->id]);
        if ($absquestion) {
            $data = [
                'id' => $absquestion->get('id'),
                'assignid' => $assignid,
                'totalmaxgrade' => $absquestion->get('totalmaxgrade'),
                'qtotal' => $absquestion->get('qtotal'),
                'totalqbonus' => $absquestion->get('totalqbonus'),
                'method' => $absquestion->get('method'),
                'validated' => $absquestion->get('validated'),
                'questioncolor' => $absquestion->get('questioncolor'),
            ];

            $data = array_merge($data, absquestion_group::fetch_data($absquestion->get('id')));
        }

        return $data;
    }
}
