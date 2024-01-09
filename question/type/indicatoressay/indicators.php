<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or qtypeify
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
 * Responsible for displaying the library list page
 *
 * @package    qtype_indicatoressay
 * @copyright  2023 onwards SysBind  {@link http://sysbind.co.il}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\plugininfo\qtype;

require_once ("../../../config.php");
require_once ($CFG->libdir . '/adminlib.php');
require_once ($CFG->dirroot . '/question/type/indicatoressay/locallib.php');

require_login(0, false);

if (!is_siteadmin()) {
    redirect(new moodle_url('/'), get_string('no_access', 'qtype_indicatoressay'), null, \core\output\notification::NOTIFY_WARNING);
    return;
}

$pageurl = new moodle_url('/question/type/indicatoressay/indicators.php');

if ($data = data_submitted()) {
    if (isset($data->indicatorsdata)) {
        $indicatorsdata = json_decode($data->indicatorsdata, true);
        $updated = qtype_indicatoressay_update_indicators($indicatorsdata);

        redirect($pageurl, get_string('updated', 'qtype_indicatoressay'));
    }
}

$PAGE->set_url($pageurl);

admin_externalpage_setup('qtype_indicatoressay_indicators');

$PAGE->set_title("{$SITE->shortname}: " . get_string('indicatorssettings', 'qtype_indicatoressay'));

echo $OUTPUT->header();

echo '<h3>' . get_string('list_all_indicators', 'qtype_indicatoressay') . '</h2>';

echo '<p>' . get_string('list_all_indicators_desc', 'qtype_indicatoressay') . '</p>';

$buttons = '';
$buttons .= html_writer::link('#', get_string('add', 'qtype_indicatoressay'), ['class' => 'btn btn-outline-info mr-2', 'id' => 'ind_addnew']);
$buttons .= html_writer::link('#', '', ['class' => 'btn btn-outline-info mr-2 disabled', 'id' => 'ind_delete']);

$buttonsdiv = html_writer::div($buttons, 'mb-4');

echo $buttonsdiv;

$indicators = qtype_indicatoressay_get_indicators();

// TODO: Remove

// $settings = get_config('qtype_mlnlpessay');
// $activecategories = get_config('qtype_mlnlpessay', 'numberofcategories');
// $i = 1;
// $tagnames = [];
// $descriptions = [];
// while ($i <= $activecategories) {
//     $categoryname = 'category' . $i . 'name';
//     if ($settings->$categoryname != '') {
//         $tagnames[] = $settings->{'tag' . $i . 'name'};
//         $descriptions[] = $settings->{'category' . $i . 'description'};
//     }
//     $i++;
// }
// $params['categories'] = $tagnames;
// $params['models'] = $descriptions;

$params = [];
$params['categories'] = [];
$params['models'] = [];
$hascapedit = (object) [];

$usedindicators = qtype_indicatoressay_get_usedindicators();

$data = [
    $indicators,
    $params,
    $hascapedit,
    $usedindicators,
];

$html = '
    <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
    <style>
    .tabulator-row:not(:hover).tabulator-selectable:not(:hover).tabulator-row-even .editablecol:not(:hover) {
        background: #99c1de;
    }
    .tabulator-row:not(:hover).tabulator-selectable:not(:hover).tabulator-row-odd .editablecol:not(:hover) {
        background: #bcd4e6;
    }
    </style>
    <form id="indicatorsform" action="' . $pageurl . '" method="POST">
    <input name="indicatorsdata" id="indicatorsdata" type="hidden" value="" />
    </form>
    <div id="indicators-table"></div>
    ';
echo $html;

$buttons = '';
$buttons .= html_writer::link('#', get_string('submit', 'qtype_indicatoressay'), ['class' => 'btn btn-success mr-2 mt-4', 'id' => 'ind_submit']);

$buttonsdiv = html_writer::div($buttons, 'mb-4');

echo $buttonsdiv;

$PAGE->requires->js_call_amd('qtype_indicatoressay/indicators', 'init', $data);

echo $OUTPUT->footer();
