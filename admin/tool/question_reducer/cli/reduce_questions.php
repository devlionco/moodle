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
 * Reduce questions CLI script.
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer;

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/adminlib.php');

$help =
    "Reduce questions within given contextid.

Options:
--contextid=INT       Context id to reduce questions in.
-h, --help            Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php admin/tool/question_reducer/cli/reduce_questions.php --contextid=100
";

list($options, $unrecognized) = cli_get_params(
    array(
        'contextid'  => null,
        'help'    => false,
    ),
    array(
        'h' => 'help',
    )
);

if ($options['help'] || $options['contextid'] === null) {
    echo $help;
    exit(0);
}

$contextid = $options['contextid'];

context_question_reducer::reduce_questions($contextid);

exit(0);
