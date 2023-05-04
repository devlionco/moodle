<?php

namespace qtype_mlnlpessay\task;

defined('MOODLE_INTERNAL') || die();

class adhoc_lambdawarmup extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'qtype_mlnlpessay';
    }

    public function execute() {
        global $CFG;
        $stopwarmup = 60 * 60;
        $sleeptime = 20;
        $cache = \cache::make('qtype_mlnlpessay', 'quizlambdawarmup');
        $inprocess = $cache->get('inprocess');
        if (empty($inprocess) || $inprocess == 0) {
            $start = time();
            while (true) {
                $cache->set('inprocess', 1);
                $cache->set('started', 1);

                if (time() - $start > $stopwarmup) {
                    mtrace('Lambda Stop warmup');
                    $cache->set('inprocess', 0);
                    $cache->delete('started', 0);
                    break;
                }

                $awsvendorpath = $CFG->vendor_aws_path;
                mtrace('Lambda Start');
                require $awsvendorpath . '/autoload.php';
                $key = get_config('qtype_mlnlpessay', 'aws_labmda_key');
                $secret = get_config('qtype_mlnlpessay', 'aws_labmda_secret');
                $region = get_config('qtype_mlnlpessay', 'aws_labmda_region');
                $functionname = get_config('qtype_mlnlpessay', 'aws_labmda_functionname');

                $payload = '{
                      "textfilepath": "text text",
                      "question_attempt": "1",
                      "categoriesids": "[1,3,5,7]",
                      "num_models": "1"
                    }';

                $client = \Aws\Lambda\LambdaClient::factory(array(
                        'credentials' => array(
                                'key' => $key,
                                'secret' => $secret,
                        ),
                        'region' => 'eu-west-1',
                ));

                $result = $client->invoke(array(
                        'FunctionName' => $functionname,
                        'Payload' => $payload,
                ));

                mtrace("Sleep for " . $sleeptime . " seconds");
                sleep($sleeptime);
            }
        }
    }
}
