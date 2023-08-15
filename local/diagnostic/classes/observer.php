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
 * Event observers.
 *
 * @package local_diagnostic
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021 Devlion.co
 */

namespace local_diagnostic;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * @param \mod_quiz\event\attempt_submitted $event
     * @return bool
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event): bool {
        global $DB, $USER, $CFG;

        $context = $event->get_context();

        $cmid = $context->instanceid;
        $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);

        $mid = $DB->get_field('local_metadata', 'data', ['instanceid' => $cmid, 'fieldid' => $metadatafieldid]);

        if (!empty($mid) && isset($CFG->cluster_activity_mid) && $CFG->cluster_activity_mid == $mid) {
            $mid = $DB->get_field('local_metadata', 'data', ['instanceid' => $cmid, 'fieldid' => $metadatafieldid]);
            $cache = \local_diagnostic\cache::get_record(['mid' => $mid]);

            $params['recache'] = true;
            $params['cache'] = $cache;
            $params['rebuild'] =  $cache ? $cache->get('rebuild') : 0;
            $params['mid'] = $mid;
            $params['cmids'] = [$cmid];
            $params['cmid'] = $cmid;

            \local_diagnostic_external::process($params);
        }

        return true;
    }
}
