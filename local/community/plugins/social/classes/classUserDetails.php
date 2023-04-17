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
 * Plugin strings are defined here.
 *
 * @package     community_social
 * @category    string
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/local/community/plugins/social/locallib.php');

class classUserDetails {
    private $userid;
    private $table;
    private $obj;

    public function __construct() {
        $this->table = 'community_social_usr_dtls';
    }

    public function set_user_id($userid) {
        global $DB;

        $this->userid = $userid;

        $row = $DB->get_record($this->table, array('userid' => $this->userid));
        if (empty($row)) {
            $objinsert = new stdClass();
            $objinsert->userid = $this->userid;
            $objinsert->timecreated = time();
            $objinsert->timemodified = time();
            $DB->insert_record($this->table, $objinsert);

            $this->obj = $DB->get_record($this->table, array('userid' => $this->userid));
        } else {
            $this->obj = $row;
        }
    }

    public function if_field_cached($field) {
        if (!empty($this->obj)) {
            if ($this->obj->$field != null) {
                return $this->obj->$field;
            } else {
                return '';
            }
        }
        return null;
    }

    public function get_count_oer_activities($flagupdate = false, $setvalue = null) {
        $activities = social_get_activities_from_oer_catalog($this->userid);
        $cachevalue = $activities['count_oercatalog_activities'];

        return $cachevalue;
    }

    public function get_colleagues($flagupdate = false, $setvalue = null) {
        global $DB;

        $field = 'colleagues';

        $cachevalue = $this->if_field_cached($field);
        if ($cachevalue !== null) {
            if ($flagupdate || $cachevalue == null) {
                if ($setvalue == null) {
                    $cachevalue = social_get_colleagues_count($this->userid);
                } else {
                    $cachevalue = $setvalue;
                }

                $this->obj->$field = $cachevalue;
                $this->obj->timemodified = time();
                $DB->update_record($this->table, $this->obj, $bulk = false);
            }
        }

        return $cachevalue;
    }

    public function get_followers($flagupdate = false, $setvalue = null) {
        global $DB;

        $field = 'followers';

        $cachevalue = $this->if_field_cached($field);
        if ($cachevalue !== null) {
            if ($flagupdate || $cachevalue == null) {
                if ($setvalue == null) {
                    $cachevalue = social_get_followers_count($this->userid);
                } else {
                    $cachevalue = $setvalue;
                }

                $this->obj->$field = $cachevalue;
                $this->obj->timemodified = time();
                $DB->update_record($this->table, $this->obj, $bulk = false);
            }
        }

        return $cachevalue;
    }

    public function get_followed($flagupdate = false, $setvalue = null) {
        global $DB;

        $field = 'followed';

        $cachevalue = $this->if_field_cached($field);
        if ($cachevalue !== null) {
            if ($flagupdate || $cachevalue == null) {
                if ($setvalue == null) {
                    $cachevalue = social_get_followed_count($this->userid);
                } else {
                    $cachevalue = $setvalue;
                }

                $this->obj->$field = $cachevalue;
                $this->obj->timemodified = time();
                $DB->update_record($this->table, $this->obj, $bulk = false);
            }
        }

        return $cachevalue;
    }

    public function get_shared_courses_in_social($flagupdate = false, $setvalue = null) {
        global $DB;

        $field = 'sharedcoursessocial';

        $cachevalue = $this->if_field_cached($field);
        if ($cachevalue !== null) {
            if ($flagupdate || $cachevalue == null) {
                if ($setvalue == null) {
                    $cachevalue = social_get_shared_courses_count($this->userid);
                } else {
                    $cachevalue = $setvalue;
                }

                $this->obj->$field = $cachevalue;
                $this->obj->timemodified = time();
                $DB->update_record($this->table, $this->obj, $bulk = false);
            }
        }

        return $cachevalue;
    }

    public function get_last_access($flagupdate = false, $setvalue = null) {
        global $DB;

        $field = 'lastaccess';

        $cachevalue = $this->if_field_cached($field);
        if ($cachevalue !== null) {
            if ($flagupdate || $cachevalue == null) {
                if ($setvalue == null) {
                    $cachevalue = social_get_lastaccess($this->userid);
                } else {
                    $cachevalue = $setvalue;
                }

                $this->obj->$field = $cachevalue;
                $this->obj->timemodified = time();
                $DB->update_record($this->table, $this->obj, $bulk = false);
            }
        }

        return $cachevalue;
    }

    public function get_peered_courses($flagupdate = false, $setvalue = null) {
        global $DB;

        $field = 'peeredcourses';

        $cachevalue = $this->if_field_cached($field);
        if ($cachevalue !== null) {
            if ($flagupdate || $cachevalue == null) {
                if ($setvalue == null) {
                    $cachevalue = social_get_user_shared_courses($this->userid);
                } else {
                    $cachevalue = $setvalue;
                }

                $this->obj->$field = $cachevalue;
                $this->obj->timemodified = time();
                $DB->update_record($this->table, $this->obj, $bulk = false);
            }
        }

        return $cachevalue;
    }

    private function get_count_used_activities_from_oer() {
        global $DB;

        $value = 0;
        $data = social_get_activities_from_oer_catalog($this->userid);
        if (isset($data['oercatalog_activities']) && !empty($data['oercatalog_activities'])) {
            $actids = array();
            foreach ($data['oercatalog_activities'] as $item) {
                $actids[] = $item->cmid;
            }

            $tmp = $DB->get_records_sql("SELECT * FROM {community_oercatalog_log} WHERE activityid IN (" . implode(',', $actids) .
                    ") GROUP BY userid");

            $value = count($tmp);
        }

        return $value;
    }

    public function get_count_used_oer_activities($flagupdate = false, $setvalue = null) {
        $cachevalue = $this->get_count_used_activities_from_oer();

        return $cachevalue;
    }

    public function get_oercatalog_activities($flagupdate = false, $setvalue = null) {
        $cachevalue = json_encode(social_get_activities_from_oer_catalog($this->userid));

        $data = json_decode($cachevalue, true);

        return $data;
    }

    public function update_teacher() {
        global $DB;

        $this->get_colleagues(true);
        $this->get_followers(true);
        $this->get_followed(true);
        $this->get_shared_courses_in_social(true);
        $this->get_last_access(true);
        $this->get_peered_courses(true);

        // Oercatalog.
        $this->get_oercatalog_activities(true);
        $this->get_count_used_oer_activities(true);
        $this->get_count_oer_activities(true);

        $this->obj->ifupdate = 0;
        $this->obj->lastupdate = time();
        $DB->update_record('community_social_usr_dtls', $this->obj);

        return true;
    }

    public static function activate_refresh($userid) {
        global $DB;

        if (!empty($userid)) {
            $row = $DB->get_record('community_social_usr_dtls', array('userid' => $userid));
            if (!empty($row)) {
                $row->ifupdate = 1;
                $DB->update_record('community_social_usr_dtls', $row);
            }
        }

        return true;
    }
}
