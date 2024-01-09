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
 * Install instructions.
 *
 * @package     qtype_indicatoressay
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_indicatoressay_install() {
    global $DB;

    // Check for empty indicators table.
    if ($indicators = $DB->get_records('qtype_indicatoressay_ind')) {
        return;
    }

    // Get mlnlp.
    $settings = get_config('qtype_mlnlpessay');

    $exist = [];
    $activecategories = get_config('qtype_mlnlpessay', 'numberofcategories');
    $i = 1;
    while ($i <= $activecategories) {
        $categoryname = 'category' . $i . 'name';
        if ($settings->$categoryname != '') {
            $ind = new stdClass();
            $ind->categoryname = $settings->$categoryname;
            $ind->tagname = $settings->{'tag' . $i . 'name'};
            $ind->categorydescription = $settings->{'category' . $i . 'description'};
            $exist[] = $ind;
        }
        $i++;
    }

    // Seed indicators table.
    $k = 1;
    foreach ($exist as $item) {
        $time = time();
        $record = new stdClass();
        $record->indicatorid = $k;
        $record->name = $item->categoryname;
        $record->model = $item->categorydescription;
        $record->category = $item->tagname;
        $record->research = 1;
        $record->visible = 1;
        $record->deleted = 0;
        $record->timecreated = $time;
        $record->timemodified = $time;

        $DB->insert_record('qtype_indicatoressay_ind', $record);
        $k++;
    }

}
