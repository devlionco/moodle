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
 * Course module viewed event.
 *
 * @package    core
 * @copyright  2013 onwards Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Abstract Course module viewed event class.
 *
 * Class for event to be triggered when a course module is viewed.
 *
 * @package    local_petel
 * @since      Moodle 3.3
 * @copyright  2013 onwards Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class timeonpage_viewed extends \core\event\base {

    /**
     * Init method.
     *
     * Please override this in extending class and specify objecttable.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'course_module';

    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed the '{$this->objecttable}' activity with " .
            "course module id '$this->contextinstanceid' for ".$this->other['timespent']." sec.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcoursemoduletimeviewed', 'local_petel');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url("/mod/$this->objecttable/view.php", array('id' => $this->contextinstanceid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, $this->objecttable, 'view', 'view.php?id=' . $this->contextinstanceid, $this->objectid,
                     $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if ($this->data['contextlevel'] === CONTEXT_COURSE) {
            $this->data['objecttable'] = 'course';
        } elseif ($this->data['contextlevel'] === CONTEXT_MODULE) {
            $cm = $this->get_module_from_cmid($this->data['objectid']);
            $this->data['objecttable'] = $cm->modname;
        }
        /*
        // Make sure this class is never used without proper object details.
        if (empty($this->objectid) || empty($this->objecttable)) {
            throw new \coding_exception('The time_on_page event must define objectid and objecttable.');
        }
        // Make sure the context level is set to module.
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
        */
    }

    private function get_module_from_cmid($cmid) { // objectid (id on course_module)
        global $DB;
        if (!$cmrec = $DB->get_record_sql(
            "SELECT cm.*, md.name as modname
                FROM {course_modules} cm
                JOIN {modules} md ON md.id = cm.module
                WHERE cm.id = ? ", array($cmid))) {
            print_error('invalidcoursemodule');
        }

        return $cmrec;
    }
}
