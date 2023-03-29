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
 * @package local_jsoneditor
 * @copyright 2022 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // New settings page.
    //$settings = new admin_settingpage('local_jsoneditor', get_string('pluginname', 'local_jsoneditor'));
    //
    //$langsettings = [
    //    1 => get_string('sitelang', 'local_jsoneditor'),
    //    2 => get_string('enlang', 'local_jsoneditor'),
    //    3 => get_string('helang', 'local_jsoneditor'),
    //];
    //
    //$settings->add(new admin_setting_configselect('local_jsoneditor/lang',
    //        get_string('settingslang', 'local_jsoneditor'),
    //        get_string('settingslangdesc', 'local_jsoneditor'),
    //        1, $langsettings)
    //);
    //
    //
    //$ADMIN->add('localplugins', $settings);
}