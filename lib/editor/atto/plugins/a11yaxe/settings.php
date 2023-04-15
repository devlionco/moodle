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
 * @package    atto_a11yaxe
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author     Oleg Ochkurenko [Devlion] <oleg@devlion.co>
 * @author     Nadav Kavalerchik [Weizmann, Science teaching department] <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_a11yaxe', new lang_string('pluginname', 'atto_a11yaxe')));

$settings = new admin_settingpage('atto_a11yaxe_settings', new lang_string('pluginname', 'atto_a11yaxe'));
if ($ADMIN->fulltree) {

    $rules = [
        'color-contrast',
        'empty-table-header',
        'frame-title-unique',
        'heading-order',
        'image-redundant-alt',
        'link-name',
        'list',
        'listitem',
        'no-autoplay-audio',
        'p-as-heading',
        'role-img-alt',
        'scrollable-region-focusable',
        'server-side-image-map',
        'svg-img-alt',
        'td-has-header',
        'th-has-data-cells',
    ];

    foreach($rules as $key => $name){
        $identeficator = 'rule__'.str_replace('-', '_', $name);

        $title = new lang_string ($identeficator, 'atto_a11yaxe');
        $desc = new lang_string ($identeficator.'_desc', 'atto_a11yaxe');
        $default = 1;

        $settings->add(new admin_setting_configcheckbox('atto_a11yaxe/'.$identeficator, $title, $desc, $default));

        if(count($rules) - 1 > $key) {
            $settings->add(new admin_setting_heading('atto_a11yaxe/' . $identeficator . '_heading', ' ', ''));
        }
    }
}
