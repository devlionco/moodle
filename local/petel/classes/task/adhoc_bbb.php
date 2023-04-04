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
 * Local plugin "petel" - Task definition
 *
 * @package    local_petel
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_petel BBB BigBlueButton WS task class.
 *
 * @package    local_social
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_bbb extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'local_petel';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {
        //global $CFG;

        $lockkey = 'petel_cron';
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_petel_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_bbb();
            $lock->release();
        }
    }

    public function run_cron_bbb() {
        global $CFG;
        // List online BBB rooms
        //
        //
        // https://docs.bigbluebutton.org/dev/api.html
        // https://github.com/bigbluebutton/bigbluebutton-api-php
        //
        // echo sha1('getMeetings'.'gcAIBNoat3WN4oRioGxY9Ik5Xrfzm3KTi3KixyCVf4');
        //
        // https://bbb.moodlemagic.info/bigbluebutton/api/getMeetings?checksum=7fd047728b989f36807310bffdc3337a08f334a5
        //
        // https://www.webhostingzone.org/ instance:
        // https://mconf.github.io/api-mate/#server=https://bbb.moodlemagic.info/bigbluebutton/&sharedSecret=3NXGBwltU24VPdTVOJrhqMyxhQIIkqZhb34QENdLx20

        //$bbb_api_getmeetings_xml = file_get_contents('https://bbb.moodlemagic.info/bigbluebutton/api/getMeetings?checksum=eedf7ac55b7b27745b26c83f4255780f366ef692');

        if (isset($CFG->bbb_server)) {
            $url = $CFG->bbb_server;
        } else {
            $url = 'https://bbb.stweizmann.org.il/bigbluebutton/api/getMeetings?checksum=eedf7ac55b7b27745b26c83f4255780f366ef692';
        }

        $curl = new \curl();
        $options['CURLOPT_PROXY'] = $CFG->proxyhost.':'.$CFG->proxyport;
        $curl->setopt($options);
        $bbb_api_getmeetings_xml = $curl->get($url);

        $xml = simplexml_load_string($bbb_api_getmeetings_xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $meeting_array = json_decode($json,TRUE);

        //var_dump($meeting_array['meetings']);
        //die;

        $bbb_usersessions = 0;
        $bbb_usersessions_video = 0;
        $bbb_usersessions_audio = 0;

        $meetingid = 0;
        $display_meeting = [];
        $meetings = [];

        if (count($meeting_array['meetings']) > 1) {
            $meetings = $meeting_array['meetings']['meeting'];
        } else {
            $meetings = $meeting_array['meetings'];
        }

        foreach($meetings as $key => $meeting) {
            //$currentmeeting = array_shift($meeting);
            //[$meeting['meetingName'], $meeting['running'], $meeting['participantCount'], $meeting['moderatorCount']];
            $display_meeting[$meetingid]['meetingName'] = $meeting['meetingName'];
            $display_meeting[$meetingid]['createDate'] = $meeting['createDate'];
            $display_meeting[$meetingid]['running'] = $meeting['running'];
            $display_meeting[$meetingid]['moderatorCount'] = $meeting['moderatorCount'];
            $display_meeting[$meetingid]['participantCount'] = $meeting['participantCount'];
            $display_meeting[$meetingid]['videoCount'] = $meeting['videoCount'];
            $display_meeting[$meetingid]['voiceParticipantCount'] = $meeting['voiceParticipantCount'];

            $meetingid++;

            $bbb_usersessions += (int)$meeting['participantCount'];
            $bbb_usersessions_video += (int)$meeting['videoCount'];
            $bbb_usersessions_audio += (int)$meeting['voiceParticipantCount'];
        }
        //var_dump($display_meeting);

        $bbb_rooms = count($meeting_array['meetings']);
        //$bbb_usersessions = 60;
        //$bbb_usersessions_video = 45;
        //$bbb_usersessions_audio = 60;

        //mtrace("debug: BBB rooms = ".$bbb_rooms );
        set_config('bbb_rooms', $bbb_rooms, 'local_petel');
        set_config('bbb_usersessions', $bbb_usersessions, 'local_petel');
        set_config('bbb_usersessions_video', $bbb_usersessions_video, 'local_petel');
        set_config('bbb_usersessions_audio', $bbb_usersessions_audio, 'local_petel');
    }

}