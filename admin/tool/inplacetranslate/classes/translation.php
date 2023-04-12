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
 * Translation class
 *
 * @module     tool/inplacetranslate
 * @package    tool
 * @subpackage inplacetranslate
 * @copyright  2020 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_inplacetranslate;

class translation {

    public function __construct() {
    }

    /**
     * Get string for translation.
     *
     */
    public static function get_string($identifier) {
        global $DB;

        require_login(null, false);
        require_capability('tool/inplacetranslate:view', \context_system::instance());

        list($stringid, $component) = explode('/', $identifier);
        if (!trim($component)) {
            $component = 'core';
        }
        list($plugintype, $pluginname) = \core_component::normalize_component($component);
        if ($pluginname) {
            $component = $plugintype . "_" . $pluginname;
        } else {
            $component = $plugintype;
        }
        $lang = current_language();

        $query = "SELECT *
                FROM {tool_inplacetranslate} ti
                WHERE ti.component = ?
                    AND ti.lang = ?
                    AND ti.stringid = ?
        ";
        $stringobj = $DB->get_record_sql($query, array($component, $lang, $stringid));
        if ($stringobj) {
            $rawstring = $stringobj->string;
        } else {
            $rawstring = get_string($stringid, $component);
        }
        $split = '/{\$a.*}/mU';
        preg_match_all($split, $rawstring, $matches, PREG_SET_ORDER, 0);
        $plain = preg_split($split, $rawstring);
        $string = '';
        if (count($plain)) {
            foreach ($plain as $i => $p) {
                $string .= '<span class="translation-tool-partstring translation-tool-editable" contenteditable="true">' . $p
                    . '</span>';
                if (isset($matches[$i][0])) {
                    $string .= '<span class="translation-tool-partstring translation-tool-noneditable" contenteditable="false">'
                        . $matches[$i][0] . '</span>';
                }
            }
        }

        $error = !(bool)$string;

        return json_encode(array(
            "error" => $error,
            "string" => $string,
        ));
    }

    /**
     * Update string.
     *
     */
    public static function update_string($identifier, $string) {
        global $DB;

        require_login(null, false);
        require_capability('tool/inplacetranslate:edit', \context_system::instance());

        $pattern = '/<span class=\"translation-tool-partstring.*>(.*)<\/span>/imU';
        $clearstring = html_entity_decode(preg_replace($pattern, '$1', $string));
        $clearstring = str_ireplace('&nbsp;', ' ', $clearstring);

        list($stringid, $component) = explode('/', $identifier);
        if (!trim($component)) {
            $component = 'core';
        }
        list($plugintype, $pluginname) = \core_component::normalize_component($component);
        if ($pluginname) {
            $component = $plugintype . "_" . $pluginname;
        } else {
            $component = $plugintype;
        }
        $lang = current_language();

        $query = "SELECT *
                FROM {tool_inplacetranslate} ti
                WHERE ti.component = ?
                    AND ti.lang = ?
                    AND ti.stringid = ?
        ";
        $stringobj = $DB->get_record_sql($query, array($component, $lang, $stringid));

        if ($stringobj) {
            $stringobj->string = $clearstring;
            $stringobj->timemodified = time();

            $res = $DB->update_record('tool_inplacetranslate', $stringobj);
        } else {
            $stringobj = new \stdClass();
            $stringobj->string = $clearstring;
            $stringobj->lang = $lang;
            $stringobj->stringid = $stringid;
            $stringobj->component = $component;
            $stringobj->timecreated = time();
			$stringobj->timemodified = $stringobj->timecreated;
            $res = $DB->insert_record('tool_inplacetranslate', $stringobj);
        }

        $sm = get_string_manager();
        $sm->reset_caches();

        $error = !(bool)$res;
        $response = !$error ? get_string('string_updated', 'tool_inplacetranslate')
            : get_string('something_wrong', 'tool_inplacetranslate');

        return json_encode(array(
            "error" => $error,
            "response" => $response
        ));
    }
}
