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
 * Site recommendations for the activity chooser.
 *
 * @package    core_course
 * @copyright  2020 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../config.php");


global $USER, $PAGE, $SITE, $CFG;

$search = optional_param('search', '', PARAM_TEXT);
$type = optional_param('type', '', PARAM_TEXT);

$context = context_system::instance();
$url = new moodle_url('/course/recommendations.php');

$pageheading = format_string($SITE->fullname, true, ['context' => $context]);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

if (isset($type) && $type == 'question') {
    require_once($CFG->dirroot . '/question/engine/bank.php');

    $PAGE->set_title(get_string('questionchooserrecommendations', 'course'));
    $PAGE->set_heading($pageheading);

    require_login();

    $renderer = $PAGE->get_renderer('core_course', 'recommendations');

    echo $renderer->header();
    echo $renderer->heading(get_string('questionchooserrecommendations', 'course'));

    $usercontext = context_user::instance($USER->id);
    $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

    $question_bank = question_bank::get_all_qtypes();
    foreach ($question_bank as $name => $qtype) {
        $data = new stdClass();
        $data->id = $data->componentname = 'qtype_' . $name;
        $data->title = $data->label = $qtype->local_name();
        $data->name = $name;
        $data->icon = $PAGE->get_renderer('question', 'bank')->qtype_icon($name);

        $recommended = $ufservice->count_favourites_by_type('core_question', 'recommend_qtype_' . $name);

        if ($recommended > 0) {
            $data->recommended = true;
        } else {
            $data->recommended = '';
        }

        $result[] = $data;
    }
    $questionlist = new \core_course\output\recommendations\question_list($result, $search);

    echo $renderer->render_question_list($questionlist);
} else {

    $PAGE->set_title(get_string('activitychooserrecommendations', 'course'));
    $PAGE->set_heading($pageheading);

    require_login();
    require_capability('moodle/course:recommendactivity', $context);

    $renderer = $PAGE->get_renderer('core_course', 'recommendations');

    echo $renderer->header();
    echo $renderer->heading(get_string('activitychooserrecommendations', 'course'));


    $manager = \core_course\local\factory\content_item_service_factory::get_content_item_service();
    if (!empty($search)) {
        $modules = $manager->get_content_items_by_name_pattern($USER, $search);
    } else {
        $modules = $manager->get_all_content_items($USER);
    }

    $activitylist = new \core_course\output\recommendations\activity_list($modules, $search);

    echo $renderer->render_activity_list($activitylist);
}
echo $renderer->footer();
