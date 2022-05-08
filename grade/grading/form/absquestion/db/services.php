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
 * gradingform_absquestion webservice definitions.
 *
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'gradingform_absquestion_save_settings' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'save_settings',
        'description' => 'Save table',
        'type'        => 'write',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_get_settings' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'get_settings',
        'description' => 'Get table',
        'type'        => 'read',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_get_comments' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'get_comments',
        'description' => 'Get comments',
        'type'        => 'read',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_get_comments_for_template' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'get_comments_for_template',
        'description' => 'Get comments for template',
        'type'        => 'read',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_set_comments' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'set_comments',
        'description' => 'Save user comments',
        'type'        => 'write',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_delete_comments' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'delete_comments',
        'description' => 'Delete user comments',
        'type'        => 'write',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_get_editor' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'get_editor',
        'description' => 'Get editor config',
        'type'        => 'read',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'gradingform_absquestion_set_info' => array(
        'classname'   => 'gradingform_absquestion\external',
        'methodname'  => 'set_info',
        'description' => 'Save question info',
        'type'        => 'write',
        'ajax'          => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);
