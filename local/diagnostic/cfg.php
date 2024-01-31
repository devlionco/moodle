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
 * Web
 *
 * @package    local_diagnostic
 * @copyright  2023 Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

require_login();
$context = \context_system::instance();
require_capability('moodle/site:config', $context);

$mid = required_param('mid', PARAM_INT);
$clusters = optional_param('clusters', 5, PARAM_INT);
$action = required_param('action', PARAM_TEXT);

switch ($action) {
    case "add":
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        if (!(in_array($mid, $croncustommids))) {
            $croncustommids[] = $mid;
        }
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        if (!(in_array($mid, $custommids))) {
            $custommids[] = $mid;
        }
        set_config('custommids', implode(',', $custommids), 'local_diagnostic');
        set_config('activityclusternum_' . $mid, $clusters, 'local_diagnostic');
        $returnurl = new \moodle_url('/admin/settings.php', ['section' => 'local_diagnostic']);
        redirect($returnurl);
        break;
    case "remove":
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        $croncustommids = array_diff($croncustommids, [$mid]);
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        $croncustommids = array_diff($custommids, [$mid]);
        set_config('custommids', implode(',', $croncustommids), 'local_diagnostic');
        $returnurl = new \moodle_url('/admin/settings.php', ['section' => 'local_diagnostic']);
        redirect($returnurl);
        break;
    case "addandrun":
        require_once $CFG->dirroot . '/local/diagnostic/classes/external.php';
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        if (!(in_array($mid, $croncustommids))) {
            $croncustommids[] = $mid;
        }
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        if (!(in_array($mid, $custommids))) {
            $custommids[] = $mid;
        }
        set_config('custommids', implode(',', $custommids), 'local_diagnostic');
        set_config('activityclusternum_' . $mid, $clusters, 'local_diagnostic');

        if ($cache = \local_diagnostic\cache::get_record(['mid' => $mid])) {
            $cache->delete();
        }

        ob_start();
        @local_diagnotic_rebuild([$mid]);
        $result = ob_get_clean();
        print_r($result);
        break;
    default:
}
