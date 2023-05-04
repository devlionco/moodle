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
 * system the code checker from the web.
 *
 * @package    qtype_mlnlpessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);

require_once __DIR__ . '/../../../config.php';
require_once($CFG->libdir . '/clilib.php');

$usage = "script that will rerender question attempts;

Usage:
    # php question/type/mlnlpessay/run.php [--cmid] 
    # php question/type/mlnlpessay/run.php [--help|-h]

Options:
    -h --help                   Print this help.
    --cmid                      Cmid of the quiz

Examples:

    # php theme/enterprise/system.php
        Does nothing
";

list($options, $unrecognised) = cli_get_params(
        [
                'cmid' => 0
        ], [
                'h' => 'help',
                'help' => 'help',
        ]
);

if ((isset($options['help']) && $options['help'] !== false) || (isset($options['h']) && $options['h'] !== false) ||
        (empty($options))) {
    cli_writeln($usage);
    exit(2);
}
if ($options['cmid']) {
    //check if atempts are exists for MLNPquestions
    $sql = "SELECT  att.*,que.id AS questionid, qas.id AS stepid
            FROM {question_attempt_steps}  qas
            JOIN {question_attempts}  att ON qas.questionattemptid = att.id
            JOIN {question}  que ON att.questionid = que.id
            JOIN {quiz_attempts}  qa ON (qa.uniqueid = att.questionusageid)
            JOIN {quiz}  q ON (qa.quiz = q.id)
            JOIN {course_modules}  cm ON (cm.instance = q.id)
            WHERE 
            cm.id= ?
            AND que.qtype = 'mlnlpessay'
            AND qas.fraction IS NOT NULL
            AND que.stamp IS NOT NULL
            AND que.length > 0
            AND qa.state = 'finished'  
            AND qa.preview = 0         ";

    $attempts = $DB->get_records_sql($sql, [$options['cmid']]);
    if (empty($attempts)) {
        cli_writeln("\033[31mNo Attempts for cmid\033[0m");
        exit(2);
    }

    foreach ($attempts as $attempt) {

        $sql = "SELECT attd.* 
                FROM mdl_question_attempt_steps atts 
                LEFT JOIN mdl_question_attempt_step_data attd ON (atts.id = attd.attemptstepid AND attd.name = 'answer')
                WHERE  atts.questionattemptid=? AND atts.state='complete' ORDER BY attd.id DESC LIMIT 1";

        $attdata = $DB->get_record_sql($sql, [$attempt->id]);
        if (empty($attdata)) {
            cli_writeln("\033[31m empty answer for qattempt " . $attempt->id . "\033[0m");
        }
        $response['answer'] = $attdata->value;
        $answertext = strip_tags($response['answer']);
        $answertext = strval(str_replace("\r\n", "", $answertext));

        $question = $DB->get_record('qtype_mlnlpessay_options', array('questionid' => $attempt->questionid));
        $categoriesweight = json_decode($question->categoriesweightteacher);
        if ($categoriesweight == '' || $categoriesweight == null) {
            $categoriesweight = (array) json_decode($question->categoriesweight);
        }
        $catgoriesnames = '';
        $categoriesids = [];
        foreach ($categoriesweight as $key => $category) {
            if (isset($category->iscategoryselected) && $category->iscategoryselected) {
                $catgoriesnames .= fn_clean($category->name) . '|';
                $categoriesids[] = $category->id;
            } else {
                unset($categoriesweight[$key]);
            }
        }
        $questionid = $attempt->questionid;
        $step = getProtectedValue($response['answer'], 'step');

        $stepid = $attempt->stepid;

        $question_attempt_id = $attempt->id;
        $question_attempt = $DB->get_record('question_attempts', ['id' => $question_attempt_id]);

        //getting number of models to run on
        $models_number = get_config('qtype_mlnlpessay', 'numberofmodels');

        //Add graderesponse task.
        $data = new stdClass;
        $data->answertext = $answertext;
        $data->catgoriesnames = $catgoriesnames;
        $data->categoriesids = json_encode($categoriesids);
        $data->categoriesweight = $categoriesweight;
        $data->question_attempt = $question_attempt;
        $data->questionid = $questionid;
        $data->step = $step;
        $data->stepid = $stepid;
        $data->models_number = $models_number;

        $task = new \qtype_mlnlpessay\task\adhoc_graderesponse();
        $task->set_custom_data(
                $data
        );
        \core\task\manager::queue_adhoc_task($task);
        cli_writeln("\033[31m generated adhoc for stepid " . $stepid . "\033[0m");

    }

    cli_writeln("\033[31m finished run for cmid: " . $options['cmid'] . "\033[0m");
}

function getProtectedValue($obj, $name) {
    $array = (array) $obj;
    $prefix = chr(0) . '*' . chr(0);
    return $array[$prefix . $name];
}

function fn_clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
