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
 * This is the external API for this component.
 *
 * @package     gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_absquestion;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use context_system;
use dml_exception;
use Exception;
use external_api;
use external_description;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;

/**
 * This is the external API for this component.
 *
 * @copyright  2019 David Monllao {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * save_settings parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function save_settings_parameters() {
        return new external_function_parameters(
            static::absquestion_save_structure()
        );
    }

    /**
     * Saves absquestion structure into database.
     *
     * @param $id
     * @param $assignid
     * @param $totalmaxgrade
     * @param $qtotal
     * @param $totalqbonus
     * @param $method
     * @param $validated
     * @param $questions
     * @param $groups
     * @param $grouppass
     * @return array status and error message
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function save_settings($id, $assignid, $totalmaxgrade, $qtotal, $totalqbonus, $method, $validated, $questions, $groups, $grouppass) {
        global $PAGE;

        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(self::save_settings_parameters(), [
                'id' => $id,
                'assignid' => $assignid,
                'totalmaxgrade' => $totalmaxgrade,
                'qtotal' => $qtotal,
                'totalqbonus' => $totalqbonus,
                'method' => $method,
                'validated' => $validated,
                'questions' => $questions,
                'groups' => $groups,
                'grouppass' => $grouppass,
            ]
        );

        $absquestion = new absquestion($id);
        $absquestion->save_data($params);

        return absquestion::fetch_data($assignid);
    }

    /**
     * save_settings return
     *
     * @since  Moodle 3.8
     * @return external_description
     */
    public static function save_settings_returns() {
        return new external_single_structure(
            static::absquestion_get_structure()
        );
    }

    /**
     * get_settings parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function get_settings_parameters() {
        return new external_function_parameters(
            array(
                'assignid' => new external_value(PARAM_INT, 'assign id', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Return the absquestion object if exists.
     *
     * @param $assignid
     * @return array data
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function get_settings($assignid) {
        global $PAGE;

        $PAGE->set_context(context_system::instance());

        self::validate_parameters(self::get_settings_parameters(), [
                'assignid' => $assignid,
            ]
        );

        return absquestion::fetch_data($assignid);
    }

    /**
     * get_settings return
     *
     * @return external_single_structure
     * @since  Moodle 3.8
     */
    public static function get_settings_returns() {
        return new external_single_structure(
            static::absquestion_get_structure()
        );
    }

    public static function absquestion_save_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'absquestion id', VALUE_REQUIRED),
            'assignid' => new external_value(PARAM_INT, 'asign id', VALUE_REQUIRED),
            'totalmaxgrade' => new external_value(PARAM_INT, 'total max grade', VALUE_REQUIRED),
            'qtotal' => new external_value(PARAM_INT, 'number of questions', VALUE_REQUIRED),
            'totalqbonus' => new external_value(PARAM_INT, 'number of bonus questions', VALUE_REQUIRED),
            'method' => new external_value(PARAM_INT, 'method', VALUE_REQUIRED),
            'validated' => new external_value(PARAM_INT, 'is validated?', VALUE_REQUIRED),
            'questions' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID of the question'),
                    'sequence' => new external_value(PARAM_INT, 'sequence num'),
                    'qmax' => new external_value(PARAM_INT, 'qmax'),
                    'bonus' => new external_value(PARAM_INT, 'bonus'),
                    'group' => new external_value(PARAM_INT, 'group'),
                    'subq' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'ID of the subquestion'),
                            'sequence' => new external_value(PARAM_INT, 'sequence num'),
                            'qmax' => new external_value(PARAM_INT, 'qmax'),
                        ])
                        ,'', VALUE_OPTIONAL)
                ])
                , '', VALUE_OPTIONAL),
            'groups' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID of the group'),
                    'sequence' => new external_value(PARAM_INT, 'sequence num'),
                    'value' => new external_value(PARAM_ALPHA, 'group letter'),
                ])
                , '', VALUE_OPTIONAL),
            'grouppass' => new external_multiple_structure(
                new external_single_structure([
                    'sequence' => new external_value(PARAM_INT, 'sequence num'),
                    'value' => new external_value(PARAM_INT, 'grouppass value'),
                ])
                , '', VALUE_OPTIONAL),
        ];
    }

    public static function absquestion_get_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'absquestion id', VALUE_OPTIONAL),
            'assignid' => new external_value(PARAM_INT, 'asign id', VALUE_OPTIONAL),
            'totalmaxgrade' => new external_value(PARAM_INT, 'total max grade', VALUE_OPTIONAL),
            'qtotal' => new external_value(PARAM_INT, 'number of questions', VALUE_OPTIONAL),
            'totalqbonus' => new external_value(PARAM_INT, 'number of bonus questions', VALUE_OPTIONAL),
            'method' => new external_value(PARAM_INT, 'method', VALUE_OPTIONAL),
            'validated' => new external_value(PARAM_INT, 'is validated?', VALUE_OPTIONAL),
            'questioncolor' => new external_value(PARAM_INT, 'is question color enabled?', VALUE_OPTIONAL),
            'questions' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID of the question'),
                    'sequence' => new external_value(PARAM_INT, 'sequence num'),
                    'qmax' => new external_value(PARAM_INT, 'qmax'),
                    'bonus' => new external_value(PARAM_INT, 'bonus'),
                    'info' => new external_value(PARAM_RAW, 'info'),
                    'group' => new external_value(PARAM_INT, 'group'),
                    'subq' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'ID of the subquestion'),
                            'sequence' => new external_value(PARAM_INT, 'sequence num'),
                            'qmax' => new external_value(PARAM_INT, 'qmax'),
                            'info' => new external_value(PARAM_RAW, 'info'),
                        ])
                        ,'', VALUE_OPTIONAL)
                ])
                , '', VALUE_OPTIONAL),
            'groups' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID of the group'),
                    'sequence' => new external_value(PARAM_INT, 'sequence num'),
                    'value' => new external_value(PARAM_ALPHA, 'group letter'),
                ])
                , '', VALUE_OPTIONAL),
            'grouppass' => new external_multiple_structure(
                new external_single_structure([
                    'sequence' => new external_value(PARAM_INT, 'sequence num'),
                    'value' => new external_value(PARAM_INT, 'grouppass value'),
                ])
                , '', VALUE_OPTIONAL),
        ];
    }

    /**
     * get_comments parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function get_comments_for_template_parameters() {
        return new external_function_parameters(
            array(
                'assignid' => new external_value(PARAM_INT, 'Assign id', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Return the Atto editor init with comments object if exists.
     *
     * @param $assignid
     * @return array data
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function get_comments_for_template($assignid) {

        self::validate_context(context_system::instance());
        self::validate_parameters(self::get_comments_parameters(), [
                'assignid' => $assignid,
            ]
        );

        return \gradingform_absquestion\absquestion_comment::get_assign_comments_for_template($assignid);
    }


    /**
     * get_comments return
     *
     * @return external_multiple_structure
     * @since  Moodle 3.8
     */
    public static function get_comments_for_template_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'questionId' => new external_value(PARAM_INT, 'ID of the question'),
                'sequence' => new external_value(PARAM_INT, 'question sequence'),
                'qorder' => new external_value(PARAM_INT, 'question sequence'),
                'max' => new external_value(PARAM_INT, 'max points'),
                'usedpoint' => new external_value(PARAM_INT, 'used points'),
                'comments' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'comment id'),
                        'text' => new external_value(PARAM_RAW, 'comment text'),
                        'points' => new external_value(PARAM_INT, 'comment points'),
                        'isglobal' => new external_value(PARAM_INT, 'is comment global'),
                    ], '', VALUE_OPTIONAL)
                ),
                'subq' => new external_multiple_structure(
                    new external_single_structure([
                        'subqId' => new external_value(PARAM_INT, 'subquestion id'),
                        'sequence' => new external_value(PARAM_INT, 'subquestion sequence'),
                        'max' => new external_value(PARAM_INT, 'max points'),
                        'usedpoint' => new external_value(PARAM_INT, 'used points'),
                        'subqComments' => new external_multiple_structure(
                            new external_single_structure([
                                'id' => new external_value(PARAM_INT, 'comment id'),
                                'text' => new external_value(PARAM_RAW, 'comment text'),
                                'points' => new external_value(PARAM_INT, 'comment points'),
                                'isglobal' => new external_value(PARAM_INT, 'is comment global'),
                            ], '', VALUE_OPTIONAL)
                        ),
                    ], '', VALUE_OPTIONAL)
                ),
            ])
            , '', VALUE_OPTIONAL);
    }


    /**
     * get_comments parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function get_comments_parameters() {
        return new external_function_parameters(
            array(
                'assignid' => new external_value(PARAM_INT, 'Assign id', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Return the Atto editor init with comments object if exists.
     *
     * @param $assignid
     * @return array data
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function get_comments($assignid) {

        self::validate_context(context_system::instance());
        self::validate_parameters(self::get_comments_parameters(), [
                'assignid' => $assignid,
            ]
        );

        return absquestion_comment_link::get_assign_comments($assignid);
    }


    /**
     * get_comments return
     *
     * @return external_multiple_structure
     * @since  Moodle 3.8
     */
    public static function get_comments_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID of the comment link'),
                'qid' => new external_value(PARAM_INT, 'question id'),
                'text' => new external_value(PARAM_RAW, 'comment html'),
                'points' => new external_value(PARAM_INT, 'points'),
                'isglobal' => new external_value(PARAM_INT, 'is global?'),
            ])
        , '', VALUE_OPTIONAL);
    }

    /**
     * set_comments parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function set_comments_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID of the comment link', VALUE_OPTIONAL),
                'assignid' => new external_value(PARAM_INT, 'assign id'),
                'qid' => new external_value(PARAM_INT, 'question id'),
                'text' => new external_value(PARAM_RAW, 'comment html'),
                'method' => new external_value(PARAM_INT, 'method'),
                'points' => new external_value(PARAM_INT, 'points'),
                'isglobal' => new external_value(PARAM_INT, 'is global?'),
            )
        );
    }

    /**
     * Set new comments for instance.
     *
     * @param $id
     * @param $qid
     * @param $text
     * @param $points
     * @param $isglobal
     * @return bool result
     * @throws \restricted_context_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function set_comments($id, $assignid, $qid, $text, $method, $points, $isglobal) {

        self::validate_context(context_system::instance());
        $params = [
            'id' => $id,
            'assignid' => $assignid,
            'qid' => $qid,
            'text' => $text,
            'method' => $method,
            'points' => $points,
            'isglobal' => $isglobal,
        ];

        $params = self::validate_parameters(self::set_comments_parameters(), $params);

        return absquestion_comment_link::set_comments($params);
    }

    /**
     * get_comments return
     *
     * @return external_value
     * @since  Moodle 3.8
     */
    public static function set_comments_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Response result', VALUE_DEFAULT, false),
                'message' => new external_value(PARAM_RAW, 'Response message', VALUE_DEFAULT, ''),
            )
        );
    }


    /**
     * set_comments parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function delete_comments_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID of the comment link')
            )
        );
    }

    /**
     * Set new comments for instance.
     *
     * @param $id
     * @param $sequence
     * @param $text
     * @param $points
     * @param $isglobal
     * @return bool result
     * @throws \restricted_context_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function delete_comments($id) {

        self::validate_context(context_system::instance());
        $params = [
            'id' => $id,
        ];

        self::validate_parameters(self::delete_comments_parameters(), $params);

        return absquestion_comment_link::delete_comments($id);
    }

    /**
     * get_comments return
     *
     * @return external_value
     * @since  Moodle 3.8
     */
    public static function delete_comments_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Response result', VALUE_DEFAULT, false),
                'message' => new external_value(PARAM_RAW, 'Response message', VALUE_DEFAULT, ''),
            )
        );
    }
    /**
     * get_editor parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function get_editor_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * get_editor return
     *
     * @return external_function_parameters
     * @since  Moodle 3.8
     */
    public static function get_editor_returns() {
        return new external_function_parameters(
            array(
                'editor' => new external_value(PARAM_RAW, 'Editor params', VALUE_DEFAULT, null),
            )
        );
    }


    /**
     * Return the Atto editor config.
     *
     * @return array data
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function get_editor() {
        global $PAGE;

        self::validate_context(context_system::instance());

        $configstr = get_config('editor_atto', 'toolbar');
        $grouplines = explode("\n", $configstr);

        $groups = array();
        foreach ($grouplines as $groupline) {
            $line = explode('=', $groupline);
            if (count($line) > 1) {
                $group = trim(array_shift($line));
                $plugins = array_map('trim', explode(',', array_shift($line)));
                $groups[$group] = $plugins;
            }
        }

        $modules = array('moodle-editor_atto-editor');
        $jsplugins = array();

        foreach ($groups as $group => $plugins) {
            $groupplugins = array();
            foreach ($plugins as $plugin) {
                // Do not die on missing plugin.
                if (!\core_component::get_component_directory('atto_' . $plugin))  {
                    continue;
                }
                // Remove manage files if requested.
                if ($plugin == 'managefiles' && isset($options['enable_filemanagement']) && !$options['enable_filemanagement']) {
                    continue;
                }

                $jsplugin = array();
                $jsplugin['name'] = $plugin;
                $jsplugin['params'] = array();
                $modules[] = 'moodle-atto_' . $plugin . '-button';

                $groupplugins[] = $jsplugin;
            }
            $jsplugins[] = array('group'=>$group, 'plugins'=>$groupplugins);
        }

        $params = array(
            'content_css' => $PAGE->theme->editor_css_url()->out(false),  // TODO check it
            'contextid' => context_system::instance()->id,
            'plugins' => $jsplugins,
        );
        $editor = ['modules' => $modules, 'params' => $params];

        return ['editor' => json_encode($editor)];
    }

    /**
     * Set info for question parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function set_info_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID of the question'),
                'info' => new external_value(PARAM_RAW, 'info text')
            )
        );
    }

    /**
     * Set info for question.
     *
     * @param $id
     * @param $info
     * @return bool result
     * @throws \restricted_context_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @since  Moodle 3.8
     */
    public static function set_info($id, $info) {

        self::validate_context(context_system::instance());
        $params = [
            'id' => $id,
            'info' => $info,
        ];

        self::validate_parameters(self::set_info_parameters(), $params);

        return absquestion_question::save_info($id, $info);
    }

    /**
     * Set info for question return
     *
     * @return external_value
     * @since  Moodle 3.8
     */
    public static function set_info_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Response result', VALUE_DEFAULT, false),
                'message' => new external_value(PARAM_RAW, 'Response message', VALUE_DEFAULT, ''),
            )
        );
    }
}
