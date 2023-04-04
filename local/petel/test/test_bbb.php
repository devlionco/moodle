<?php
define('CLI_SCRIPT', true);
include(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

global $CFG;
$CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
$CFG->debugdisplay = true;             // NOT FOR PRODUCTION SERVERS!
$CFG->debugdeveloper = true;
echo 'Get BBB stats...'.PHP_EOL;
//$bbb_api_getmeetings_xml = file_get_contents('https://bbb.moodlemagic.info/bigbluebutton/api/getMeetings?checksum=eedf7ac55b7b27745b26c83f4255780f366ef692');
$url = 'https://bbb.moodlemagic.info/bigbluebutton/api/getMeetings?checksum=eedf7ac55b7b27745b26c83f4255780f366ef692';

$curl = new \curl();
$options['CURLOPT_PROXY'] = $CFG->proxyhost.':'.$CFG->proxyport;
//echo $CFG->proxyhost.':'.$CFG->proxyport;
$curl->setopt($options);
$bbb_api_getmeetings_xml = $curl->get($url);

$xml = simplexml_load_string($bbb_api_getmeetings_xml, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$meeting_array = json_decode($json,TRUE);

//var_dump($meeting_array['meetings']);
echo 'meetings='.count($meeting_array['meetings']);
//echo 'context='.$meeting_array['meetings']['metadata']['contextid'];
var_dump($meeting_array);
echo PHP_EOL;