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

use core_question\output\qbank_chooser_item;

require_once("../../config.php");

$search = optional_param('search', '', PARAM_TEXT);

$context = context_system::instance();
$url = new moodle_url('/local/petel/recommendations.php');

$pageheading = format_string($SITE->fullname, true, ['context' => $context]);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$PAGE->set_title(get_string('questionchooserrecommendations', 'local_petel'));
$PAGE->set_heading($pageheading);

require_login();
require_capability('moodle/course:recommendactivity', $context);

$renderer = $PAGE->get_renderer('local_petel', 'recommendations');

echo $renderer->header();
echo $renderer->heading(get_string('questionchooserrecommendations', 'local_petel'));

$qtypes = array_map(function ($qtype) use ($context) {
    return new qbank_chooser_item($qtype, $context);
}, question_bank::get_creatable_qtypes());

$questions = [];

$config = get_config('local_petel', 'question_recommendation');
$default = [];

if(!empty($config)){
    $default = json_decode($config, true);
}

$default = array_flip($default);

foreach($qtypes as $qname => $item){
    $flag = true;
    $search = trim($search);
    if(!empty($search) && strpos(strtolower($item->value), strtolower($search)) === false && strpos(strtolower($item->label), strval(strtolower($search))) === false){
        $flag = false;
    }

    if($flag) {
        $obj = new \StdClass();
        $obj->id = $item->value;
        $obj->name = $item->value;
        $obj->title = $item->label;
        $obj->icon = $OUTPUT->render($item->icon);
        $obj->componentname = 'qtype_' . $item->value;
        $obj->help = $item->description->out();
        $obj->recommended = isset($default[$item->value]) ? true : false;

        $questions[] = $obj;
    }
}

$questionlist = new \local_petel\output\recommendations\question_list($questions, $search);

echo $renderer->render_question_list($questionlist);

echo $renderer->footer();
