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
 * Plugin administration pages are defined here.
 *
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // New settings page.
    $page = new admin_settingpage('local_redmine', get_string('pluginname', 'local_redmine', null, false));

    if ($ADMIN->fulltree) {
        // Redmine url.
        $name = 'local_redmine/redmineurl';
        $title = get_string('redmineurl', 'local_redmine');
        $description = get_string('redmineurldesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine username.
        $name = 'local_redmine/redmineusername';
        $title = get_string('redmineusername', 'local_redmine');
        $description = get_string('redmineusernamedesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Redmine password.
        $name = 'local_redmine/redminepassword';
        $title = get_string('redminepassword', 'local_redmine');
        $description = get_string('redminepassworddesc', 'local_redmine');
        $default = '';
        $setting = new admin_setting_configpasswordunmask($name, $title, $description, $default);
        $page->add($setting);
    }
    // Add settings page to the local settings category.
    $ADMIN->add('localplugins', $page);
}