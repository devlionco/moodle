<?php

/**
 * Atto text editor integration version file.
 *
 * @package    atto_bdi
 * @copyright  2022 The Open University of Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise the js strings required for this module.
 */
function atto_bdi_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('selectionrequired'),
                                    'atto_bdi');
}
