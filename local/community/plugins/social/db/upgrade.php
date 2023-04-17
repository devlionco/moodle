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
 * Local plugin "community_social" - Upgrade plugin tasks
 *
 * @package    community_social
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_community_social_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019081906) {

        // Rename tables for petel.
        if ($dbman->table_exists('local_social_user_details') && $dbman->table_exists('local_social_shared_courses')
                && $dbman->table_exists('local_social_collegues') && $dbman->table_exists('local_social_followers')
                && $dbman->table_exists('local_social_requests')) {

            $table = new xmldb_table('local_social_user_details');
            $dbman->rename_table($table, 'community_social_usr_dtls');

            $table = new xmldb_table('local_social_shared_courses');
            $dbman->rename_table($table, 'community_social_shrd_crss');

            $table = new xmldb_table('local_social_collegues');
            $dbman->rename_table($table, 'community_social_collegues');

            $table = new xmldb_table('local_social_followers');
            $dbman->rename_table($table, 'community_social_followers');

            $table = new xmldb_table('local_social_requests');
            $dbman->rename_table($table, 'community_social_requests');

            return true;
        }

        // Community_socail savepoint reached.
        upgrade_plugin_savepoint(true, 2019081906, 'community', 'social');
    }

    if ($oldversion < 2019081918) {

        $rows = $DB->get_records('community_social_usr_dtls');

        foreach ($rows as $row) {
            $row->ifupdate = 1;
            $DB->update_record('community_social_usr_dtls', $row);
        }

        // Community_socail savepoint reached.
        upgrade_plugin_savepoint(true, 2019081918, 'community', 'social');
    }

    if ($oldversion < 2019081919) {

        $rows = $DB->get_records('community_social_requests');

        foreach ($rows as $obj) {
            $obj->status = 1;
            $DB->update_record('community_social_requests', $obj);
        }

        // Community_socail savepoint reached.
        upgrade_plugin_savepoint(true, 2019081919, 'community', 'social');
    }

    return true;
}
