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
 * Plugin general functions are defined here.
 *
 * @package     qtype_mlnlpessay
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function get_category_types() {
    $settings = get_config('qtype_mlnlpessay');

    $texttypes = preg_split("/\r\n|\n|\r/", $settings->categorytypes);

    $types = [];
    $prefix = 'categorytype';

    $i = 1;
    foreach ($texttypes as $key => $type) {
        if ($type == '') {
            continue;
        }
        $types[$prefix . $i] = $type;
        $i++;
    }

    $types[''] = '—';

    return $types;
}

function hascapedit($courseid, $userid) {

    if (is_siteadmin()) {
        return true;
    }

    $context = context_course::instance($courseid);
    $editroles = array('manager');
    foreach (get_user_roles($context, $userid) as $item) {
        if (in_array($item->shortname, $editroles)) {
            return true;
        }
    }

    return false;
}

function check_response($questionid, $questionattemptid) {
    global $DB;
    $response = null;

    $response = $DB->get_record('qtype_mlnlpessay_response',
            array('questionid' => $questionid, 'questionattemptid' => $questionattemptid), '*');

    return $response;
}

function get_response($pythonfeedbacksql, $questiondata = []) {
    global $DB, $OUTPUT;
    $response = null;
    $pythonfeedback = (array) json_decode($pythonfeedbacksql->pythonresponse);

    // TABLE.
    $categories = [];

    $settings = get_config('qtype_mlnlpessay');
    $types = get_category_types();

    foreach ($pythonfeedback as $key => $category) {
        $category->type = isset($category->type) ? $types[$category->type] : '';
        $catid = $category->id + 1;
        $category->name = $settings->{'category' . $catid . 'name'};
        $category->description = $settings->{'category' . $catid . 'description'};
        $categories[] = $category;
    }

    $data = new stdClass;
    $data->categories = $categories;
    $data->qid = $questiondata['qid'];
    $data->questionattemptid = $pythonfeedbacksql->questionattemptid;
    $data->questionid = $pythonfeedbacksql->questionid;
    $cmid = $DB->get_field_sql("SELECT cm.id FROM {course_modules} cm
                                        JOIN {modules} m ON cm.module = m.id
                                        JOIN {quiz} q ON cm.instance = q.id
                                        JOIN {quiz_attempts} qa ON q.id = qa.quiz
                                        WHERE m.name = ? AND qa.id = ?",
            ['quiz', $pythonfeedbacksql->quizattemptid]);

    $data->showoverridden = $cmid ? has_capability('qtype/mlnlp:edit', \context_module::instance($cmid)) : false;

    $truefeedback = html_writer::start_tag('div', ['class' => 'mlnlpessay-container-' . $pythonfeedbacksql->questionid]);
    $truefeedback .= $OUTPUT->render_from_template('qtype_mlnlpessay/responsetable', $data);
    $truefeedback .= html_writer::end_tag('div');

    $response = $truefeedback;

    return $response;
}

function get_enabled_categories($questionid) {
    global $DB;

    $return = [];

    if ($question = $DB->get_record('qtype_mlnlpessay_options', array('questionid' => $questionid))) {
        $categories = $question->categoriesweightteacher ? json_decode($question->categoriesweightteacher) :
                json_decode($question->categoriesweight);
        foreach ($categories as $cat) {
            if ($cat->iscategoryselected) {
                $return[$cat->id] = $cat;
            }
        }
    }

    return $return;
}

/**
 * @param $event
 * @return false|void
 * @throws coding_exception
 * @throws dml_exception
 */
function lambdawarmup($event) {
    global $DB;
    $quiz = isset($event->get_data()['other']['quizid']) ? $event->get_data()['other']['quizid'] : 0;
    if (empty($quiz)) {
        return;
    }

    //check if it has mlnlpquestion
    $sql = "SELECT qa.id
            FROM {quiz_slots} AS qa 
            LEFT JOIN {question} AS q ON (qa.questionid = q.id)
            WHERE qa.quizid = :quizid AND q.qtype = 'mlnlpessay' limit 1;";

    if (!$DB->get_record_sql($sql, ['quizid' => $quiz])) {
        return false;
    }

    // Initial cache.
    $cache = \cache::make('qtype_mlnlpessay', 'quizlambdawarmup');
    $started = $cache->get('started');
    if (empty($started)) {
        $task = new \qtype_mlnlpessay\task\adhoc_lambdawarmup();
        $task->set_custom_data([]);
        \core\task\manager::queue_adhoc_task($task);
        $cache->set('started', 1);
    }
}