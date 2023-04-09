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
 * Metadata multiselect fieldtype plugin language file.
 *
 * @package metadatafieldtype_multiselect
 * @subpackage metadatafieldtype_multiselect
 * @author Tamir Hajaj <tamir@sysbind.co.il>
 * @copyright 2022 Tamir Hajaj {@link https://sysbind.co.il}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'multiselect metadata fieldtype';
$string['displayname'] = 'Dropdown multiselect';
$string['privacy:metadata'] = 'Fieldtypes do not store data.';
$string['notinrightformat'] = 'Not in the right format, format should be like - &ltvalue&gt:&ltlang1&gt=&ltstring1&gt[optinal]: |&ltlang2&gt=&ltstring2&gt|...';
$string['checkifmultiselectornot'] = 'Filed will be as multiselect';
$string['duplicatevalues'] = 'there are some duplicate values.';
$string['rightformat_help'] = "the format should be like values and after then '=' sign. if you want to add more language that
installed in moodle system you have to use '|' - <value\>:<lang1\>=<string1\>[optinal]: |<lang2\>=<string2\>|...";
$string['rightformat'] = 'Write the keys and the values?';
