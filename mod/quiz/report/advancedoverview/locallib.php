<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     quiz_advancedoverview
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function quiz_advancedoverview_get_question_link($question, $cmid) {
    $params['id'] = $question->id;
    $params['cmid'] = $cmid;
    $editurl = new moodle_url('/question/bank/editquestion/question.php', $params);

    return $editurl;
}
