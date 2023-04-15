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
 * @copyright  2021 Tamir hajaj <tamir.hajaj@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Set params for this plugin
 * @param string $elementid
 */
function atto_a11yaxe_params_for_js($elementid, $options, $fpoptions) {

    $rules = [
        'area-alt',
        'aria-allowed-attr',
        'aria-command-name',
        'aria-hidden-body',
        'aria-hidden-focus',
        'aria-input-field-name',
        'aria-meter-name',
        'aria-progressbar-name',
        'aria-required-attr',
        'aria-required-children',
        'aria-required-parent',
        'aria-roledescription',
        'aria-roles',
        'aria-toggle-field-name',
        'aria-tooltip-name',
        'aria-valid-attr-value',
        'aria-valid-attr',
        'audio-caption',
        'blink',
        'button-name',
        'bypass',
        'color-contrast',
        'definition-list',
        'dlitem',
        'document-title',
        'duplicate-id-active',
        'duplicate-id-aria',
        'duplicate-id',
        'form-field-multiple-labels',
        'frame-focusable-content',
        'frame-title',
        'html-has-lang',
        'html-lang-valid',
        'html-xml-lang-mismatch',
        'image-alt',
        'input-button-name',
        'input-image-alt',
        'label',
        'link-name',
        'list',
        'listitem',
        'marquee',
        'meta-refresh',
        'nested-interactive',
        'no-autoplay-audio',
        'object-alt',
        'role-img-alt',
        'scrollable-region-focusable',
        'select-name',
        'server-side-image-map',
        'svg-img-alt',
        'td-headers-attr',
        'th-has-data-cells',
        'valid-lang',
        'video-caption',
        'autocomplete-valid',
        'avoid-inline-spacing',
        'accesskeys',
        'aria-allowed-role',
        'aria-dialog-name',
        'aria-text',
        'aria-treeitem-name',
        'empty-heading',
        'empty-table-header',
        'frame-tested',
        'frame-title-unique',
        'heading-order',
        'image-redundant-alt',
        'label-title-only',
        'landmark-banner-is-top-level',
        'landmark-complementary-is-top-level',
        'landmark-contentinfo-is-top-level',
        'landmark-main-is-top-level',
        'landmark-no-duplicate-banner',
        'landmark-no-duplicate-contentinfo',
        'landmark-no-duplicate-main',
        'landmark-one-main',
        'landmark-unique',
        'meta-viewport-large',
        'meta-viewport',
        'page-has-heading-one',
        'presentation-role-conflict',
        'region',
        'scope-attr-valid',
        'skip-link',
        'tabindex',
        'table-duplicate-name',
        'color-contrast-enhanced',
        'identical-links-same-purpose',
        'meta-refresh-no-exceptions',
        'css-orientation-lock',
        'focus-order-semantics',
        'hidden-content',
        'label-content-name-mismatch',
        'link-in-text-block',
        'p-as-heading',
        'table-fake-caption',
        'td-has-header',
    ];

    $checks = [];
    foreach($rules as $name){
        $identeficator = 'rule__'.str_replace('-', '_', $name);
        $config = get_config('atto_a11yaxe', $identeficator);

        $checks[] = $config == 1 ? ['id' => $name, 'enabled' => true] : ['id' => $name, 'enabled' => false];
    }

    return array(
        'checks' => json_encode($checks),
    );
}

/**
 * Initialise this plugin
 * @param string $elementid
 */
function atto_a11yaxe_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('nowarnings',
                                    'allgood',
                                    'there_are',
                                    'problems',
                                    'report',
                                    'dialog_showme',
                                    'dialog_close',
                                    'dialog_next',
                                    'dialog_previous',
                                    'imagesmissingalt',
                                    'needsmorecontrast',
                                    'needsmoreheadings',
                                    'tableswithmergedcells',
                                    'tablesmissingcaption',
                                    'emptytext',
                                    'entiredocument',
                                    'tablesmissingheaders'),
                                    'atto_a11yaxe');
}

//*   $PAGE->requires->jquery();
//*   $PAGE->requires->jquery_plugin('ui');
//*   $PAGE->requires->jquery_plugin('ui-css');

//function atto_a11yaxe_before_http_headers() {
//    global $PAGE;
//    $PAGE->requires->css('/lib/editor/atto/plugins/a11yaxe/jquery-ui.css');
//}

//doesnt work - i have told nadav
//function atto_a11yaxe_before_headers() {
//    global $PAGE;
//    $PAGE->requires->css('/lib/editor/atto/plugins/a11yaxe/jquery-ui.css');
//}

//crushes - Coding error detected, it must be fixed by a programmer: Cannot require a CSS file after <head> has been printed.
//function atto_a11yaxe_before_footer() {
//    global $PAGE;
//    $PAGE->requires->css('/lib/editor/atto/plugins/a11yaxe/jquery-ui.css');
//}



