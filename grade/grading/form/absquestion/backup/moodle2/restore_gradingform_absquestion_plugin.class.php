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
 * Support for restore API
 *
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restores the absquestion specific data from grading.xml file
 *
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_gradingform_absquestion_plugin extends restore_gradingform_plugin {

    /**
     * Declares the absquestion XML paths attached to the form definition element
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_definition_plugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('gradingform_absquestion_criterion',
                $this->get_pathfor('/absquestioncriteria/absquestion'));

        $paths[] = new restore_path_element('gradingform_absquestion_group',
                $this->get_pathfor('/absquestioncriteria/absquestion/groups/group'));

        $paths[] = new restore_path_element('gradingform_absquestion_question',
                $this->get_pathfor('/absquestioncriteria/absquestion/questions/question'));

        $paths[] = new restore_path_element('gradingform_absquestion_commentlink',
                $this->get_pathfor('/absquestioncriteria/absquestion/questions/question/comments/comment'));

        return $paths;
    }

    /**
     * Declares the absquestion XML paths attached to the form instance element
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_instance_plugin_structure() {

        $paths = array();

        //TODO RESTORE FILES
        /*
        $paths[] = new restore_path_element('gradinform_absquestion_filling',
            $this->get_pathfor('/fillings/filling'));
        */
        return $paths;
    }

    /**
     * Processes criterion element data
     *
     * Sets the mapping 'gradingform_absquestion_criterion' to be used later
     *
     * @param stdClass|array $data
     */
    public function process_gradingform_absquestion_criterion($data) {
        global $DB, $USER;

        $data = (object) $data;
        $oldid = $data->id;
        $data->definitionid = $this->get_new_parentid('grading_definition');

        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        $newid = $DB->insert_record('absquestion', $data);
        $this->set_mapping('gradingform_absquestion_criterion', $oldid, $newid);
    }

    /**
     * Processes group element data
     *
     * Sets the mapping 'gradingform_absquestion_group' to be used later by
     * {@link self::process_gradinform_absquestion_question()}
     *
     * @param stdClass|array $data
     */
    public function process_gradingform_absquestion_group($data) {
        global $DB, $USER;

        $data = (object) $data;
        $oldid = $data->id;
        $data->absid = $this->get_new_parentid('gradingform_absquestion_criterion');

        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        $newid = $DB->insert_record('absquestion_group', $data);
        $this->set_mapping('gradingform_absquestion_group', $oldid, $newid);
    }

    /**
     * Processes group element data
     *
     * Sets the mapping 'gradingform_absquestion_question' to be used later by
     * {@link self::process_gradinform_absquestion_question()}
     *
     * @param stdClass|array $data
     */
    public function process_gradingform_absquestion_question($data) {
        global $DB, $USER;

        $data = (object) $data;
        $oldid = $data->id;

        $data->absid = $this->get_new_parentid('gradingform_absquestion_criterion');
        if ($data->absgid > 0) {
            $data->absgid = $this->get_mappingid('gradingform_absquestion_group', $data->absgid, 0);
        }
        if ($data->parentid > 0) {
            $data->parentid = $this->get_mappingid('gradingform_absquestion_question', $data->parentid, 0);
        }

        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        $newid = $DB->insert_record('absquestion_question', $data);
        $this->set_mapping('gradingform_absquestion_question', $oldid, $newid);
    }

    /**
     * Processes comment element data
     *
     *
     * @param stdClass|array $data
     */
    public function process_gradingform_absquestion_commentlink($data) {
        global $DB, $USER;

        $data = (object) $data;

        $sql = "SELECT instance FROM {course_modules} cm 
                    LEFT JOIN {context} c ON c.instanceid = cm.id 
                    LEFT JOIN {grading_areas} ga ON c.id = ga.contextid 
                        WHERE ga.id = ?";

        $assignid = $DB->get_field_sql($sql, [$this->get_new_parentid('grading_area')]);

        if (!$assignid) {
            return;
        }

        $data->assignid = $assignid;
        $data->absqid = $this->get_new_parentid('gradingform_absquestion_question');

        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        $DB->insert_record('absquestion_comment_link', $data);
    }

    /**
     * Processes filling element data
     *
     * @param stdClass|array $data
     */

    /*TODO
    public function process_gradinform_absquestion_filling($data) {
        global $DB;

        $data = (object)$data;
        $data->instanceid = $this->get_new_parentid('grading_instance');
        $data->criterionid = $this->get_mappingid('gradingform_absquestion_criterion', $data->criterionid);
        $data->levelid = $this->get_mappingid('gradingform_absquestion_level', $data->levelid);

        if (!empty($data->criterionid)) {
            $DB->insert_record('gradingform_absquestion_fillings', $data);
        }

    }
    */
}
