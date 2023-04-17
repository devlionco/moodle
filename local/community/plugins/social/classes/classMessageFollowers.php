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
 * The community_social.
 *
 * @package     community_sharewith
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class MessageFollowers {

    private $followers;
    private $userid;

    public function __construct($sourceuserid, $activityid, $courseid) {
        global $DB;

        $this->sourceuserid = $sourceuserid;
        $this->activity_id = $activityid;
        $this->course_id = $courseid;
        $this->followers = $DB->get_records('local_social_followers', array('followuserid' => $sourceuserid, 'isactive' => 1));
    }

    public function sendmessagetofollowers() {

        if (!empty($this->followers)) {
            foreach ($this->followers as $row) {
                $this->messageToUser($row->userid);
            }
        }
    }

    public function messagetouser($teacherid) {
        global $DB, $USER;

        $a = new stdClass;
        $a->activity_name = $this->activity_id;
        $a->teacher_name = $USER->firstname . ' ' . $USER->lastname;
        $subject = get_string('subject_message_for_teacher', 'community_sharewith', $a);

        $message = '';

        $objinsert = new stdClass();
        $objinsert->useridfrom = $this->sourceuserid;
        $objinsert->useridto = $teacherid;
        $objinsert->subject = $subject;
        $objinsert->fullmessage = $message;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = '';
        $objinsert->smallmessage = get_string('info_message_for_teacher', 'community_sharewith');
        $objinsert->notification = 1;
        $objinsert->timecreated = time();
        $objinsert->component = 'local_social';
        $objinsert->eventtype = 'social_activity_shared';
        $messageid = $DB->insert_record('message', $objinsert);

        $objinsert = new stdClass();
        $objinsert->messageid = $messageid;
        $objinsert->isread = 0;
        $DB->insert_record('message_popup', $objinsert);

        // Save in activities_sharing_shared.
        $objinsert = new stdClass();
        $objinsert->useridto = $teacherid;
        $objinsert->useridfrom = isset($this->sourceuserid) ? $this->sourceuserid : "";
        $objinsert->courseid = $this->course_id;
        $objinsert->activityid = $this->activity_id;
        $objinsert->messageid = $messageid;
        $objinsert->restoreid = null;
        $objinsert->source = 'social_notification';
        $objinsert->complete = 0;
        $objinsert->timecreated = time();

        $rowid = $DB->insert_record('activities_sharing_shared', $objinsert);

        // Update full message and fullmessagehtml.
        $a = new stdClass;
        $a->restore_id = $rowid;
        $fullmessage = get_string('fullmessagehtml_for_teacher', 'community_sharewith', $a);

        $obj = new stdClass();
        $obj->id = $messageid;
        $obj->fullmessage = $message;
        $obj->fullmessagehtml = $fullmessage;
        $DB->update_record('message', $obj);
    }
}
