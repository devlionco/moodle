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
 * Algebra question type upgrade code.
 *
 * @package    qtype_mlnlpessay
 * @copyright  Dor Herbesman - Devlion team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_mlnlpessay_upgrade($oldversion) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020061504) {
        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('categoriesweight', XMLDB_TYPE_TEXT, '10', XMLDB_UNSIGNED, null, null, null, 'filetypeslist');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2020061504, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2020061505) {
        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('categoriesweightteacher', XMLDB_TYPE_TEXT, '10', XMLDB_UNSIGNED, null, null, null,
                'filetypeslist');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061505, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2020061512) {
        $table = new xmldb_table('qtype_mlnlpessay_response');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('questionattemptid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('quizattemptid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('pythonresponse', XMLDB_TYPE_TEXT, 10, null, false, null,);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexqid = new xmldb_index('questionid', XMLDB_INDEX_NOTUNIQUE, array('questionid'));
        $dbman->add_index($table, $indexqid);

        $indexqaid = new xmldb_index('questionattemptid', XMLDB_INDEX_UNIQUE, array('questionattemptid'));
        $dbman->add_index($table, $indexqaid);

        $indexquizaid = new xmldb_index('quizattemptid', XMLDB_INDEX_NOTUNIQUE, array('quizattemptid'));
        $dbman->add_index($table, $indexquizaid);

        upgrade_plugin_savepoint(true, 2020061512, 'qtype', 'mlnlpessay');

    }

    if ($oldversion < 2022052001) {
        $value = get_string('svgfeedbacktemplate', 'qtype_mlnlpessay');
        set_config('svgfeedbacktemplate', $value, 'qtype_mlnlpessay');

        upgrade_plugin_savepoint(true, 2022052001, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2022061405) {

        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'categoriesweight');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timecreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('qtype_mlnlpessay_response');

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'pythonresponse');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timecreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('qtype_mlnlpessay_task');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2022061405, 'qtype', 'mlnlpessay');

    }

    if ($oldversion < 2023042800) {

        // Define field minwordlimit to be added to qtype_essay_options.
        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('minwordlimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'responsefieldlines');

        // Conditionally launch add field minwordlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('maxwordlimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'minwordlimit');

        // Conditionally launch add field maxwordlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('maxbytes', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, '0', 'responsetemplateformat');

        // Conditionally launch add field maxbytes.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Essay savepoint reached.
        upgrade_plugin_savepoint(true, 2023042800, 'qtype', 'mlnlpessay');
    }

    return true;
}
