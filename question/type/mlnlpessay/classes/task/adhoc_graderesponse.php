<?php

namespace qtype_mlnlpessay\task;

use question_engine;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/question/engine/lib.php';
require_once $CFG->dirroot . '/question/type/mlnlpessay/locallib.php';

class adhoc_graderesponse extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'qtype_mlnlpessay';
    }

    public function execute() {
        global $DB, $CFG;

        $data = $this->get_custom_data();
        $categoriesweight = $data->categoriesweight;
        $questionid = $data->questionid;
        $qa = $data->question_attempt->id;
        $answertext = $data->answertext;
        $question_attempt = $data->question_attempt;
        $models_number = $data->models_number;
        $categoriesids = $data->categoriesids;

        mtrace('answertext:');
        mtrace('==========================================');
        mtrace($answertext);
        mtrace('==========================================');

        // Checking for empty answer = Wrong answer immediately w/o any further checks.
        $answertext = trim(str_replace('&nbsp;', ' ', $answertext));

        // Checking for question attempt.
        $question_attempt_id = $question_attempt->id;
        mtrace('Checking for question attempt = ' . $question_attempt_id);
        $question_attempt = $DB->get_record('question_attempts', ['id' => $question_attempt_id]);
        if (!$question_attempt) {
            mtrace('question_attempt does not exist. Exiting.');
            return;
        }
        mtrace('question_attempt');
        mtrace(json_encode(json_decode($question_attempt), JSON_UNESCAPED_UNICODE));

        if ($answertext != '') {
            $categories = get_enabled_categories($questionid);
            $processingmode = get_config('qtype_mlnlpessay', 'processing_mode');
            switch ($processingmode) {
                case '0': // Random
                    $output = [];
                    foreach ($categories as $cat) {
                        $tag = get_config('qtype_mlnlpessay', 'tag' . ($cat->id + 1) . 'name');
                        $output[$tag] = random_int(0, 1);
                    }
                    mtrace(" Generating random respons in qtype_mlnlp_wo_python mode: " . json_encode($output, 1));
                    $output = (object) $output;
                    break;

                case '1': // Local
                    mtrace('Local mode');
                    $moodledatapath = $CFG->dataroot;
                    if (!empty($CFG->mlnlpdebug)) {
                        try {
                            $path = make_writable_directory($moodledatapath . '/mlnlpdata');
                        } catch (\moodle_exception $e) {
                            mtrace($e->getMessage());
                        }

                    } else {
                        try {
                            $path = make_temp_directory(random_string());
                        } catch (\moodle_exception $e) {
                            mtrace($e->getMessage());
                        }
                    }

                    $script = $CFG->libdir . '/../question/type/mlnlpessay/scripts/calc.py';
                    $pathtopython = get_config('core', 'pathtopython');
                    if ($pathtopython) {
                        $script = $pathtopython . ' ' . $script;
                    }

                    try {
                        $executestart = time();
                        $result_filename = 'o_' . $qa . "_" . $executestart . '.json';
                        $outputfilepath = $path . '/' . $result_filename;
                        $fp = fopen($outputfilepath, 'w');
                        fclose($fp);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    try {
                        $text_filename = 't_' . $qa . "_" . $executestart . '.txt';
                        $textfilepath = $path . '/' . $text_filename;
                        $fp = fopen($textfilepath, 'w');
                        fwrite($fp, $answertext);
                        fclose($fp);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    try {
                        $log_filename = 'o_' . $qa . "_" . $executestart . '.log';
                        $logfilepath = $path . '/' . $log_filename;
                        $fp = fopen($logfilepath, 'w');
                        fclose($fp);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    mtrace(" $script '$outputfilepath' '$moodledatapath' '$textfilepath' '$qa' '$categoriesids' > '$logfilepath' 2>&1 ");

                    try {
                        shell_exec(" $script '$outputfilepath' '$moodledatapath' '$textfilepath' '$qa' '$categoriesids' '$models_number' > '$logfilepath' 2>&1 ");
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    $file = fopen($outputfilepath, "r");
                    if (!$file) {
                        mtrace("Error in opening file: " . $outputfilepath);
                    }
                    mtrace("output file path: " . $outputfilepath);

                    try {
                        $filesize = filesize($outputfilepath);
                        $filetext = fread($file, $filesize);
                        fclose($file);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    $output = json_decode($filetext);

                    foreach ($output as $key => $value) {
                        mtrace('output for categoyid: ' . $key . ' => ' . $value);
                    }
                    break;
                case '2': // AWS Lambda
                    mtrace('AWS Lambda mode');

                    $awsvendorpath = $CFG->vendor_aws_path;
                    mtrace($awsvendorpath);

                    try {
                        mtrace($awsvendorpath . '/autoload.php');
                        require $awsvendorpath . '/autoload.php';

                        $key = get_config('qtype_mlnlpessay', 'aws_labmda_key');
                        $secret = get_config('qtype_mlnlpessay', 'aws_labmda_secret');
                        $region = get_config('qtype_mlnlpessay', 'aws_labmda_region');
                        $functionname = get_config('qtype_mlnlpessay', 'aws_labmda_functionname');

                        $cattemp = [];
                        foreach (json_decode($categoriesids) as $cat) {
                            $tag = get_config('qtype_mlnlpessay', 'tag' . ($cat + 1) . 'name');
                            $cattemp[] = $tag;
                        }

                        $payload = '{
                                  "textfilepath": "' . $answertext . '",
                                  "question_attempt": "' . $qa . '",
                                  "categoriesids": ' . json_encode($cattemp) . ',
                                  "num_models": "' . $models_number . '"
                                }';

                        mtrace($payload);

                        $client = \Aws\Lambda\LambdaClient::factory(array(
                                'credentials' => array(
                                        'key' => $key,
                                        'secret' => $secret,
                                ),
                                'region' => $region,
                        ));

                        $result = $client->invoke(array(
                                'FunctionName' => $functionname,
                                'Payload' => $payload,
                        ));
                        mtrace($result);
                        $resboby = $result['Payload'];
                        mtrace('Lambda response body');
                        mtrace(json_encode(json_decode($resboby), JSON_UNESCAPED_UNICODE));
                        $output = json_decode($resboby);

                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    break;
            }
        } else {
            // If answertext == '' -> WRONG ANSWER.
            $output = [];
            foreach ($categoriesweight as $catn) {
                $output[$catn->id] = 0;
            }
            mtrace('Genegarating wrong answer cos of empty answer');
            $output = (object) $output;
        }
        $fraction = 0;
        $feedback = [];

        $question_attempt_id = $question_attempt->id;
        $question_attempt = $DB->get_record('question_attempts', ['id' => $question_attempt_id]);
        mtrace('question_attempt');
        mtrace(json_encode($question_attempt));

        $quizattempt = $DB->get_record('quiz_attempts', ['uniqueid' => $question_attempt->questionusageid]);
        mtrace('quizattempt');
        mtrace(json_encode($quizattempt));

        $mlnlpresponseparams = [
            'questionid' => $questionid,
            'questionattemptid' => $question_attempt_id,
            'quizattemptid' => $quizattempt->id,
        ];

        $overriddenpythonresponse = [];
        if ($mlnlpresponse = $DB->get_record('qtype_mlnlpessay_response', $mlnlpresponseparams)) {
            foreach ($currentpythonresponse = json_decode($mlnlpresponse->pythonresponse) as $currentpythonresp) {
                if (isset($currentpythonresp->overridden) && !empty($currentpythonresp->overridden)) {
                    $overriddenpythonresponse[$currentpythonresp->id] = $currentpythonresp->correct;
                }
            }
        }

        foreach ($categoriesweight as $catid => $category) {
            $tag = get_config('qtype_mlnlpessay', 'tag' . ($catid + 1) . 'name');
            $catgrade = $output->$tag;
            $overridden = 0;
            if (isset($overriddenpythonresponse[$catid])) {
                $catgrade = $overriddenpythonresponse[$catid];
                $overridden = 1;
            }

            $fraction += (int) $category->weight * (int) $catgrade / 100;
            $feedback[] = [
                'name' => $category->name,
                'id' => $category->id,
                'sortorder' => $category->sortorder,
                'type' => $category->type,
                'correct' => trim($catgrade),
                'overriden' => $overridden
            ];
        }

        mtrace('Feedback');
        mtrace(json_encode(json_decode($feedback), JSON_UNESCAPED_UNICODE));

        $mlnlpessay_response = new stdClass();
        $mlnlpessay_response->questionid = $questionid;
        $mlnlpessay_response->questionattemptid = $question_attempt_id;
        $mlnlpessay_response->quizattemptid = $quizattempt->id;
        $mlnlpessay_response->pythonresponse = json_encode($feedback);
        $mlnlpessay_response->timemodified = time();
        $mlnlpessay_response->timecreated = time();
        mtrace('mlnlpessay_response');
        mtrace(json_encode(json_decode($mlnlpessay_response), JSON_UNESCAPED_UNICODE));

        if ($mlnlpresponse = $DB->get_record('qtype_mlnlpessay_response', $mlnlpresponseparams)) {
            $mlnlpessay_response->id = $mlnlpresponse->id;
            $mlnlpresponseupdated = $DB->update_record('qtype_mlnlpessay_response', $mlnlpessay_response);
            mtrace('qtype_mlnlpessay_response updated');
            mtrace(json_encode(json_decode($mlnlpresponseupdated), JSON_UNESCAPED_UNICODE));
        } else {
            $inserted = $DB->insert_record('qtype_mlnlpessay_response', $mlnlpessay_response);
            mtrace('qtype_mlnlpessay_response inserted');
            mtrace(json_encode(json_decode($inserted), JSON_UNESCAPED_UNICODE));
        }

        static::regrade_attempt_by_questionattempt($question_attempt_id, $fraction);

        return array($fraction, $feedback);
    }

    public static function regrade_attempt_by_questionattempt($questionattemptid, $fraction) {
        global $DB;

        //update grade for question after giving feedback.
        $question_attempt_step =
            $DB->get_record_select(
                'question_attempt_steps',
                'questionattemptid = ? AND fraction IS NOT NULL',
                [$questionattemptid]);
        mtrace('question_attempt_step');
        mtrace(json_encode($question_attempt_step));

        if ($question_attempt_step) {
            $question_attempt_step->fraction = $fraction;
            $updated = $DB->update_record('question_attempt_steps', $question_attempt_step);
            mtrace('question_attempt_steps updated');
            mtrace(json_encode($updated));
        }

        $questionattempt = $DB->get_record('question_attempts', ['id' => $questionattemptid]);
        $quba = \question_engine::load_questions_usage_by_activity($questionattempt->questionusageid);
        mtrace('question_attempt updated');
        mtrace(json_encode(json_decode($questionattempt), JSON_UNESCAPED_UNICODE));

        $quizattempt = $DB->get_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
        mtrace('quiz_attempts');
        $student_user = $DB->get_record('user', ['id' => $quizattempt->userid]);
        mtrace('Student (user): ' . fullname($student_user));
        mtrace(json_encode(json_decode($quizattempt), JSON_UNESCAPED_UNICODE));

        $quizattempt->sumgrades = $quba->get_total_mark();
        $quizattempt->timemodified += 1;
        $updated2 = $DB->update_record('quiz_attempts', $quizattempt);
        mtrace('quiz_attempts updated');
        mtrace(json_encode(json_decode($updated2), JSON_UNESCAPED_UNICODE));

        //DO NOT SAVE HISTORY REGRADED fraction for mlnlpessay question
        $DB->delete_records('quiz_overview_regrades', ['questionusageid' => $questionattempt->questionusageid]);
    }
}
