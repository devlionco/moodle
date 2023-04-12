<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * lib.php.
 *
 * @package     tool_inplacetranslate
 * @copyright   2021 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function tool_inplacetranslate_custom_language_translation(&$cstring, $component, $lang) {
    global $DB;
    $strings = $DB->get_records_menu('tool_inplacetranslate', ['lang' => $lang, 'component' => $component], '', 'stringid, string');
    $cstring = array_merge($cstring, $strings);
}

function tool_inplacetranslate_render_navbar_output() {
    global $PAGE;
    $strings = optional_param('strings', 0, PARAM_INT);
    if ($strings AND has_capability('tool/customlang:view', context_system::instance())) {
        $PAGE->requires->js_call_amd('tool_inplacetranslate/translate-tool', 'init', array());
    }
    return  '';
}
