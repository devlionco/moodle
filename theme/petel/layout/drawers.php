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
 * A drawer based layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2021 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

user_preference_allow_ajax_update('drawer-open-index', PARAM_BOOL);
user_preference_allow_ajax_update('drawer-open-block', PARAM_BOOL);

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {

    // Custom navigation.

    // PTL-9414.
    if ($PAGE->pagetype == 'mod-quiz-view') {
        if ($cmid = optional_param('id', 0, PARAM_INT)) {
            $context = context_module::instance($cmid);

            if (has_capability('mod/quiz:manage', $context)) {
                $advancedoverviewurl = new moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'advancedoverview'));
                $PAGE->secondarynav->add(get_string('advancedoverviewlink', 'theme_petel'), $advancedoverviewurl);
            }
        }
    }

    // PTL-9609.
    // Exclude links from menu.
    if (!has_capability('moodle/site:config', \context_system::instance())) {
        $exclude = [
            'filtermanagement',
            'filtermanage',
            'roleoverride',
            'backup',
            'restore',
            'metadata',
            'questionbank',
        ];

        $lists = $PAGE->secondarynav->get_children_key_list();
        foreach ($exclude as $key) {
            if (in_array($key, $lists)) {
                $PAGE->secondarynav->children->remove($key);
            }
        }
    }

    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    // PTL-9578.
    // $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
    // PTL-9578.
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
}

$primary = new theme_petel\navigation\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;

// PTL-9578.
if(strpos($PAGE->pagetype, 'mod-') !== false){
    $headercontent = false;
} else {
    $headercontent = $header->export_for_template($renderer);
}

$abouturl = get_config('theme_petel', 'abouturl') != '' ? get_config('theme_petel', 'abouturl') : 'https://petel.weizmann.ac.il/';
$policies = theme_petel_get_policies();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
    'abouturl' => $abouturl,
    'policies' => $policies,
];

echo $OUTPUT->render_from_template('theme_boost/drawers', $templatecontext);
