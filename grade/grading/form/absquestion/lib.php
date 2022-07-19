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
 * Grading method controller for the absquestion plugin
 *
 * @package    gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use gradingform_absquestion\absquestion;
use gradingform_absquestion\absquestion_question;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/grading/form/lib.php');

const GRADINGFORM_ABSQUESTION_METHOD_ACCUMULATIVE = 1;
const GRADINGFORM_ABSQUESTION_METHOD_SUBSTRACTIVE = 2;

/**
 * This controller encapsulates the absquestion grading logic
 *
 * @package    gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_absquestion_controller extends gradingform_controller {

    const ABSQUESTION = 'absquestion';

    public function is_form_available() {
        return true;
    }

    public function is_form_defined() {
        global $PAGE;

        if ($PAGE->pagetype == 'grade-grading-manage') {
            $output = $PAGE->get_renderer('core_grading');
            echo $output->container_end();
            echo $output->footer();
            die();
        } else {
            $definition = $this->get_definition();
            return !empty(\gradingform_absquestion\absquestion::get_record(['definitionid' => $definition->id, 'validated' => 1]));
        }
    }

    public function extend_navigation(global_navigation $navigation, navigation_node $modulenode=null) {
        global $CFG, $PAGE, $OUTPUT, $DB;

        if (!has_capability('moodle/grade:managegradingforms', $PAGE->context)) {
            return;
        }

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
                if (!\core_component::get_component_directory('atto_' . $plugin)) {
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

                component_callback('atto_' . $plugin, 'strings_for_js');
                $extra = component_callback('atto_' . $plugin, 'params_for_js', array('test', [], []));

                if ($extra) {
                    $jsplugin = array_merge($jsplugin, $extra);
                }
                // We always need the plugin name.
                $PAGE->requires->string_for_js('pluginname', 'atto_' . $plugin);

                $groupplugins[] = $jsplugin;
            }
            $jsplugins[] = ['group' => $group, 'plugins' => $groupplugins];
        }

        $pagesworking = [
            'mod-assign-view' => '#page-mod-assign-view #intro',
            'grade-grading-manage' => '#page-grade-grading-manage div.actions'
        ];

        if (!in_array($PAGE->pagetype, array_keys($pagesworking))) {
            return;
        }

        // Check if groups submittions is enabled.
        $assign = $DB->get_record('assign', array('id' => $PAGE->cm->instance));
        $area = $DB->get_record('grading_areas', array('contextid' => $PAGE->context->id, 'activemethod' => \gradingform_absquestion_controller::ABSQUESTION));
        if (empty($assign) || empty($area)) {
            return;
        }

        $qtotalmax = absquestion::QTOTALMAXDEFAULT;
        $subqnummax = absquestion::SUBQNUMMAXDEFAULT;
        $hassubmissions = false;

        if ($definition = absquestion::fetch_definition($assign->id)) {
            if ($absquestion = absquestion::get_record(['definitionid' => $definition->id])) {
                $qtotalmax = $absquestion->get('qtotalmax');
                $subqnummax = $absquestion->get('subqnummax');
                $questions = $DB->get_fieldset_select(absquestion_question::TABLE, 'id', 'absid = ?', [$absquestion->get('id')]);
                if ($questions) {
                    list($insql, $inparams) = $DB->get_in_or_equal($questions, SQL_PARAMS_NAMED, 'absqid');
                    $inparams['draft'] = 0;
                    $hassubmissions = $DB->record_exists_select(
                        'assignfeedback_editpdf_absq',
                        "questionid $insql AND draft = :draft",
                        $inparams
                    );
                }

            }
        }

        $PAGE->requires->js_call_amd('gradingform_absquestion/main', 'init',
            [
                $pagesworking[$PAGE->pagetype],
                $assign->id,
                $assign->grade,
                $qtotalmax,
                $subqnummax,
                $hassubmissions
            ]
        );

        return;
    }

    /**
     * Returns the absquestion plugin renderer
     *
     * @param moodle_page $page the target page
     * @return gradingform_absquestion_renderer
     */
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('gradingform_'. $this->get_method_name());
    }

    /**
     * Returns the HTML code displaying the preview of the grading form
     *
     * @param moodle_page $page the target page
     * @return string
     */
    public function render_preview(moodle_page $page) {

        return '';
    }

    /**
     * Deletes the absquestion definition and all the associated information
     */
    protected function delete_plugin_definition() {
        global $DB;

        // Get the list of instances.
        $instances = array_keys($DB->get_records('grading_instances', array('definitionid' => $this->definition->id), '', 'id'));
        // Delete instances.
        $DB->delete_records_list('grading_instances', 'id', $instances);
        // Delete absquestions.
        $DB->delete_records('gradingform_absquestion', array('definitionid' => $this->definition->id));
    }

    // Full-text search support.

    /**
     * Prepare the part of the search query to append to the FROM statement
     *
     * @param string $gdid the alias of grading_definitions.id column used by the caller
     * @return string
     */
    public static function sql_search_from_tables($gdid) {
        return " LEFT JOIN {gradingform_absquestion} a ON (a.definitionid = $gdid)";
    }

    /**
     * @return array An array containing a single key/value pair with the 'absquestion_criteria' external_multiple_structure.
     * @see gradingform_controller::get_external_definition_details()
     * @since Moodle 2.5
     */
    public static function get_external_definition_details() {

        return ['json' => new external_value(PARAM_RAW, 'json data', VALUE_OPTIONAL)];
    }

}

/**
 * Class to manage one absquestion grading instance.
 *
 * Stores information and performs actions like update, copy, validate, submit, etc.
 *
 * @package    gradingform_absquestion
 * @copyright   Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_absquestion_instance extends gradingform_instance {

    /** @var array stores the absquestion */
    protected $absquestion;

    protected $assign;

    public function __construct($controller, $data) {
        global $CFG;
        require_once($CFG->dirroot . '/question/editlib.php');

        parent::__construct($controller, $data);
        $this->absquestion = \gradingform_absquestion\absquestion::get_record(['definitionid' => $data->definitionid]);
        $context = $controller->get_context();
        if ($context->contextlevel == CONTEXT_MODULE) {
            list($module, $cm) = get_module_from_cmid($context->instanceid);
            $this->assign = $module;
        }
    }

    /**
     * Calculates the grade to be pushed to the gradebook
     *
     * @return float|int the valid grade from $this->get_controller()->get_grade_range()
     */
    public function get_grade() {
        global $DB;
        $grade = $totalmaxgrade = 0;
        if ($this->absquestion) {
            $absmaxgrade = $this->absquestion->get('totalmaxgrade');
            $totalmaxgrade = $this->assign->grade;
            $gradeid = $this->get_data('itemid');

            if ($this->absquestion->get('groupnum')) {
                $points = 0;
                $addedcomments = $DB->get_records('assignfeedback_editpdf_absq', ['gradeid' => $gradeid, 'draft' => 1]);
                $groupsmax = [];
                foreach ($addedcomments as $addedcomment) {
                    $question = \gradingform_absquestion\absquestion_question::get_record(['id' => $addedcomment->questionid]);
                    $groupid = $question->get('absgid');
                    if ($groupid > 0) {
                        if (!isset($groupsmax[$groupid])) {
                            $group = \gradingform_absquestion\absquestion_group::get_record(['id' => $groupid]);
                            $groupsmax[$groupid] = [
                                'count' => 0,
                                'max' => $group->get('grouppass')
                            ];
                            if ($groupsmax[$groupid]['count'] < $groupsmax[$groupid]['max']) {
                                $points += $addedcomment->points;
                                $groupsmax[$groupid]['count']++;
                            }
                        }
                    } else {
                        // Not in group.
                        $points += $addedcomment->points;
                    }
                }
            } else {
                $points = array_sum(
                    $DB->get_fieldset_select(
                        'assignfeedback_editpdf_absq',
                        'points',
                        'gradeid = ? AND draft = ?',
                        [$gradeid, 1]
                    )
                );
            }

            switch ($this->absquestion->get('method')) {
                case GRADINGFORM_ABSQUESTION_METHOD_SUBSTRACTIVE:
                    $points = $absmaxgrade - $points > 0 ? $absmaxgrade - $points : 0;
                    break;
                default:
                    break;
            }

            $grade = round($points / $absmaxgrade * $totalmaxgrade);
        }

        return $grade > $totalmaxgrade ? $totalmaxgrade : $grade;
    }

    /**
     * Returns html for form element of type 'grading'.
     *
     * @param moodle_page $page
     * @param MoodleQuickForm_grading $gradingformelement
     * @return string
     */
    public function render_grading_element($page, $gradingformelement) {
        global $OUTPUT, $DB, $PAGE;

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
                if (!\core_component::get_component_directory('atto_' . $plugin)) {
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

                component_callback('atto_' . $plugin, 'strings_for_js');
                $extra = component_callback('atto_' . $plugin, 'params_for_js', array('test', [], []));

                if ($extra) {
                    $jsplugin = array_merge($jsplugin, $extra);
                }
                // We always need the plugin name.
                $PAGE->requires->string_for_js('pluginname', 'atto_' . $plugin);

                $groupplugins[] = $jsplugin;
            }
            $jsplugins[] = ['group' => $group, 'plugins' => $groupplugins];
        }

        $gradinginstance = $gradingformelement->get_gradinginstance();

        $controller = $gradinginstance->get_controller();
        $gradeid = $gradinginstance->get_data('itemid');

        $context = $controller->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        $groupedcomments = \gradingform_absquestion\absquestion_comment::get_assign_comments_for_template($cm->instance, $gradeid);

        // Build mapper for questions and its parents.
        $mapper = [];
        foreach ($groupedcomments as $grouppedcomment) {
            $mapper[$grouppedcomment->questionId] = 0;
            foreach ($grouppedcomment->subq as $subquestioncomment) {
                $mapper[$subquestioncomment->subqId] = $grouppedcomment->questionId;
            }
        }

        // Check if groups submittions is enabled.
        $assign = $DB->get_record('assign', array('id' => $PAGE->cm->instance));
        $area = $DB->get_record(
            'grading_areas',
            [
                'contextid' => $PAGE->context->id,
                'activemethod' => \gradingform_absquestion_controller::ABSQUESTION
            ]
        );
        if (empty($assign) || empty($area)) {
            return;
        }

        $definition = $controller->get_definition();
        $absquestion = absquestion::get_record(['definitionid' => $definition->id]);

        $qtotalmax = $absquestion ? $absquestion->get('qtotalmax') : absquestion::QTOTALMAXDEFAULT;
        $subqnummax = $absquestion ? $absquestion->get('subqnummax') : absquestion::SUBQNUMMAXDEFAULT;
        $questioncolor = $absquestion ? $absquestion->get('questioncolor') : 0;
        $method = $absquestion ? $absquestion->get('method') : 0;

        $settings = (object)[
            $assign->id,
            $assign->grade,
            $qtotalmax,
            $subqnummax,
            $questioncolor,
            $method,
            $gradeid,
            $mapper
        ];

        $settings = json_encode($settings);
        $PAGE->requires->js("/grade/grading/form/absquestion/amd/build/grade.min.js");

        return "<div data-settings='{$settings}' id='root_absolute_q' class='grade'>"
            . $OUTPUT->render_from_template('gradingform_absquestion/gradecomments', ['data' => $groupedcomments])
            . "<div id='add_comment_block'></div></div>";
    }
}

/**
 * Inject the color checkbox.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function gradingform_absquestion_coursemodule_standard_elements($formwrapper, $mform) {
    global $CFG, $COURSE;

    if ($cmdata = $formwrapper->get_coursemodule()) {
        $modinfo = get_fast_modinfo($COURSE);
        $cm = $modinfo->get_cm($cmdata->id);
        $modname = $cm->get_module_type_name();

        $controller = absquestion::fetch_controller($cm->__get('instance'));
        if ($modname->get_component() == 'assign' && !is_null($controller)) {
            $definition = absquestion::fetch_definition($cm->__get('instance'));

            // From now on we are sure that this is assign and absquestion is enabled.
            $mform->addElement('header', 'absquestionsection', get_string('absquestionsection', 'gradingform_absquestion'));
            $mform->addElement('advcheckbox', 'questioncolor', get_string('questionscolorheader', 'gradingform_absquestion'));

            $choices = [];

            for ($i = 0; $i < 11; $i++) {
                $choices[$i] = $i;
            }

            $mform->addElement('select', 'qtotalmax', get_string('qtotalmax', 'gradingform_absquestion'), $choices);
            $mform->addElement('select', 'subqnummax', get_string('subqnummax', 'gradingform_absquestion'), $choices);

            if ($absquestion = absquestion::get_record(['definitionid' => $definition->id])) {
                $mform->setDefault('questioncolor', $absquestion->get('questioncolor'));
                $mform->setDefault('qtotalmax', $absquestion->get('qtotalmax'));
                $mform->setDefault('subqnummax', $absquestion->get('subqnummax'));
            }
        }
    }
}

/**
 * Hook the add/edit of the course module.
 *
 * @param stdClass $data Data from the form submission.
 * @param stdClass $course The course.
 */
function gradingform_absquestion_coursemodule_edit_post_actions($data, $course) {

    if (!isset($data->questioncolor)) {
        return $data;
    }

    $definition = absquestion::fetch_definition($data->instance);

    if ($absquestion = absquestion::get_record(['definitionid' => $definition->id])) {
        $absquestion->set('questioncolor', $data->questioncolor);
        $absquestion->set('qtotalmax', $data->qtotalmax);
        $absquestion->set('subqnummax', $data->subqnummax);
        $absquestion->update();
    } else {
        $absquestiondata = [
            'definitionid' => $definition->id,
            'qtotal' => 0,
            'groupnum' => 0,
            'method' => 0,
            'totalqbonus' => 0,
            'totalmaxgrade' => 0,
            'validated' => 0,
            'questioncolor' => $data->questioncolor,
            'qtotalmax' => $data->qtotalmax,
            'subqnummax' => $data->subqnummax,
        ];

        $absquestion = new absquestion(0, (object) $absquestiondata);
        $absquestion->create();
    }

    return $data;
}
