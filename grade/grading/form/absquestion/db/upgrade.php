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
 * Plugin upgrade steps are defined here.
 *
 * @package     gradingform_absquestion
 * @category    upgrade
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

use \gradingform_absquestion\absquestion;

/**
 * Execute gradingform_absquestion upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_gradingform_absquestion_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    if ($oldversion < 2022032400) {
        $table = new \xmldb_table('absquestion_comment_link');
        $field = new \xmldb_field('absqid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'assignid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $comments = $DB->get_records(\gradingform_absquestion\absquestion_comment_link::TABLE);
        foreach ($comments as $comment) {
            $definition = \gradingform_absquestion\absquestion::fetch_definition($comment->assignid);
            $absquestion = \gradingform_absquestion\absquestion::get_record(['definitionid' => $definition->id]);
            if ($absquestion) {
                if ($question = \gradingform_absquestion\absquestion_question::get_record(['absid' => $absquestion->get('id'), 'sequence' => $comment->qsequence])) {
                    $DB->set_field(\gradingform_absquestion\absquestion_comment_link::TABLE, 'absqid', $question->get('id'), ['id' => $comment->id]);
                }
            }
        }

        $field = new \xmldb_field('qsequence');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    }

    if ($oldversion < 2022041100) {
        $table = new \xmldb_table('absquestion_question');
        $field = new \xmldb_field('info', XMLDB_TYPE_TEXT, null, null, null, null, null, 'bonus');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2022042501) {
        $table = new \xmldb_table('absquestion');
        $field = new \xmldb_field('questioncolor', XMLDB_TYPE_INTEGER, 1, true, true, false, 0, 'validated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2022050201) {
        $table = new \xmldb_table('absquestion');
        $field = new \xmldb_field('qtotalmax', XMLDB_TYPE_INTEGER, 1, true, true, false, absquestion::QTOTALMAXDEFAULT, 'questioncolor');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new \xmldb_field('subqnummax', XMLDB_TYPE_INTEGER, 1, true, true, false, absquestion::SUBQNUMMAXDEFAULT, 'qtotalmax');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
