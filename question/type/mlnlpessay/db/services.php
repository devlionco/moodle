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
 * Format moetiles web services defintions
 *
 * @package   qtype_mlnlpessay
 * @category  event
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'qtype_mlnlpessay_get_feedback' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'get_feedback',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'get sections data',
        'ajax' => true,

    ),

    'qtype_mlnlpessay_set_override' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'set_override',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'set overridden grade',
        'write' => true,
        'ajax' => true,
    ),
);