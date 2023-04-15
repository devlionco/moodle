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
 * @package    atto_a11ycolors
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author     Oleg Ochkurenko [Devlion] <oleg@devlion.co>
 * @author     Nadav Kavalerchik [Weizmann, Science teaching department] <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function atto_a11ycolors_render_navbar_output() {
    global $PAGE;

    $PAGE->requires->js_amd_inline('require(["jquery", "atto_a11ycolors/tinycolor"], function($, tinycolor) {M.tinycolor =  tinycolor});');
    return;
}

/**
 * Set params for this plugin
 * @param string $elementid
 */
function atto_a11ycolors_params_for_js($elementid, $options, $fpoptions) {

    $availablecolors = get_config('atto_a11ycolors', 'availablecolors');
    $possiblecolors = explode("\n", (str_replace("\r", '', $availablecolors)));
    $arrcolors = [];
    foreach($possiblecolors as $color) {
        if (preg_match('/^#?([0-9a-fA-F]{3})|([0-9a-fA-F]{6})$/', $color, $matches)) {
            $arrcolors[] = $color;
        }
    }

    $availablecolorsbackground = get_config('atto_a11ycolors', 'availablecolorsbackground');
    $possiblecolorsbackground = explode("\n", (str_replace("\r", '', $availablecolorsbackground)));
    $arrcolorsbackground = array();
    foreach($possiblecolorsbackground as $color) {
        if (preg_match('/^#?([0-9a-fA-F]{3})|([0-9a-fA-F]{6})$/', $color, $matches)) {
            $arrcolorsbackground[] = $color;
        }
    }
    
    $colors = [];
    foreach($arrcolors as $row){
        $tmp = [];
        foreach(explode(' ', $row) as $color){
            if(!empty(trim($color))){
                $tmp[] = trim($color);
            }
        }
        $colors[] = $tmp;
    }

    $colorsbackground = [];
    foreach($arrcolorsbackground as $row){
        $tmp = [];
        foreach(explode(' ', $row) as $color){
            if(!empty(trim($color))){
                $tmp[] = trim($color);
            }
        }

        $colorsbackground[] = $tmp;
    }

    return array(
        'colors' => $colors,
        'colorsbackground' => $colorsbackground,
    );
}

/**
 * Initialise the js strings required for this module.
 */
function atto_a11ycolors_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('custom',
                                          'customcolor',
                                          'submit',
                                          'rgb',
                                          'hsl',
                                          'hexadecimal',
                                          'saturation',
                                          'luminance',
                                          'modaltitle'),
                                    'atto_a11ycolors');
}


