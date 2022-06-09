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
 * Support for backup API
 *
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines absquestion backup structures
 *
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_gradingform_absquestion_plugin extends backup_gradingform_plugin {

    /**
     * Declares rubric structures to append to the grading form definition
     */
    protected function define_definition_plugin_structure() {

        // Append data only if the grand-parent element has 'method' set to 'rubric'
        $plugin = $this->get_plugin_element(null, '../../method', 'absquestion');

        // Create a visible container for our data
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect our visible container to the parent
        $plugin->add_child($pluginwrapper);

        // Define our elements

        $criteria = new backup_nested_element('absquestioncriteria');

        $criterion = new backup_nested_element('absquestion', ['id'],
                [
                        'qtotal',
                        'groupnum',
                        'method',
                        'totalqbonus',
                        'totalmaxgrade',
                        'validated',
                        'questioncolor',
                        'qtotalmax',
                        'subqnummax',
                ]
        );

        $groups = new backup_nested_element('groups');

        $group = new backup_nested_element('group', ['id'],
                [
                        'sequence',
                        'name',
                        'grouppass'
                ]
        );

        $questions = new backup_nested_element('questions');

        $question = new backup_nested_element('question', ['id'],
                [
                        'absgid',
                        'sequence',
                        'parentid',
                        'qmax',
                        'bonus',
                        'info',
                ]
        );

        $commentlinks = new backup_nested_element('comments');
        $commentlink = new backup_nested_element('comment', ['id'],
                [
                        'absqcid'
                ]
        );

        // Build elements hierarchy

        $pluginwrapper->add_child($criteria);
        $criteria->add_child($criterion);
        $criterion->add_child($groups);
        $groups->add_child($group);
        $criterion->add_child($questions);
        $questions->add_child($question);
        $question->add_child($commentlinks);
        $commentlinks->add_child($commentlink);

        // Set sources to populate the data

        $criterion->set_source_table('absquestion',
                ['definitionid' => backup::VAR_PARENTID]);

        $group->set_source_table('absquestion_group',
                ['absid' => backup::VAR_PARENTID]);

        $question->set_source_sql('SELECT *
                FROM {absquestion_question} WHERE absid = :absid ORDER BY parentid ASC',
                ['absid' => backup::VAR_PARENTID]);

        $commentlink->set_source_table('absquestion_comment_link',
                ['assignid' => backup::VAR_ACTIVITYID, 'absqid' => backup::VAR_PARENTID]);
        // no need to annotate ids or files yet (one day when criterion definition supports
        // embedded files, they must be annotated here)

        return $plugin;
    }

    /**
     * Declares rubric structures to append to the grading form instances
     */
    protected function define_instance_plugin_structure() {

        //TODO HERE WE SHALL PROCESS FILES DATA
        // Append data only if the ancestor 'definition' element has 'method' set to 'rubric'
        $plugin = $this->get_plugin_element(null, '../../../../method', 'absquestion');

        return $plugin;
    }
}

