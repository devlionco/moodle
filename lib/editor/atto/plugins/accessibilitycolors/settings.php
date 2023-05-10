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
 * Atto text editor integration version file.
 *
 * @package    atto_accessibilitycolors
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author     Oleg Ochkurenko [Devlion] <oleg@devlion.co>
 * @author     Nadav Kavalerchik [Weizmann, Science teaching department] <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_accessibilitycolors', new lang_string('pluginname', 'atto_accessibilitycolors')));

$settings = new admin_settingpage('atto_accessibilitycolors_settings', new lang_string('pluginname', 'atto_accessibilitycolors'));
if ($ADMIN->fulltree) {

    // Color font.
    $name = new lang_string('availablecolors', 'atto_accessibilitycolors');
    $desc = new lang_string('availablecolors_desc', 'atto_accessibilitycolors');
    $default = '#F10C0E #0078A4 #5D141E #0E3447 #671C05 #0C3810 #002151 #23164F #000000
#FFED00 #30A5BF #922C48 #236B7C #B53F0C #3D683C #0B4886 #554283 #585858
#FF4DCF #99C9DA #C37280 #659BB1 #F56D18 #6F9C6C #4F80BB #8E7BB8 #B2B2B2
#C2F940 #CCE4EC #E8C6C8 #B8CBD8 #F8D8BC #CCE3CA #D2DBEF #E1DBF2 #E2E1E1
#17EFFF #EBF4F8 #F2EDEE #F1F1F1 #FAF5F0 #F0F3EF #EAF1FB #F5F2F7 #FFFFFF';
    $setting = new admin_setting_configtextarea('atto_accessibilitycolors/availablecolors', $name, $desc, $default);
    $settings->add($setting);

    // Color background.
    $name = new lang_string('availablecolorsbackground', 'atto_accessibilitycolors');
    $desc = new lang_string('availablecolorsbackground_desc', 'atto_accessibilitycolors');
    $default = '#F10C0E #0078A4 #5D141E #0E3447 #671C05 #0C3810 #002151 #23164F #000000
#FFED00 #30A5BF #922C48 #236B7C #B53F0C #3D683C #0B4886 #554283 #585858
#FF4DCF #99C9DA #C37280 #659BB1 #F56D18 #6F9C6C #4F80BB #8E7BB8 #B2B2B2
#C2F940 #CCE4EC #E8C6C8 #B8CBD8 #F8D8BC #CCE3CA #D2DBEF #E1DBF2 #E2E1E1
#17EFFF #EBF4F8 #F2EDEE #F1F1F1 #FAF5F0 #F0F3EF #EAF1FB #F5F2F7 #FFFFFF';
    $setting = new admin_setting_configtextarea('atto_accessibilitycolors/availablecolorsbackground', $name, $desc, $default);
    $settings->add($setting);
}
