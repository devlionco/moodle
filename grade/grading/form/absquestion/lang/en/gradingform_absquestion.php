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
 * Plugin strings are defined here.
 *
 * @package     gradingform_absquestion
 * @category    string
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Absolute question grading';
$string['buttontitle'] = 'Open table for editing';

$string['title'] = 'Title';
$string['general_settings'] = 'General settings';
$string['maximum_grade'] = 'Maximum grade';
$string['total_questions'] = 'Total Questions';
$string['total_questions_groups'] = 'Total Questions Groups';
$string['grading_methods'] = 'Grading Methods';
$string['question'] = 'Question';
$string['noquestion'] = 'No question found or no ID specified';
$string['questions'] = 'Questions';
$string['cancel'] = 'Cancel';
$string['sub_question'] = 'Sub Question';
$string['max_points'] = 'Max Points';
$string['bonus'] = 'Bonus';
$string['question_groups'] = 'Question Groups';
$string['n_of_sub_questions'] = 'N. off sub questions';
$string['branch'] = 'Branch';
$string['points'] = 'Points';
$string['group'] = 'Group';
$string['from'] = 'from';
$string['total'] = 'Total';
$string['total_bonuses_questions'] = 'Total bonuses questions';
$string['total_question_to_choose'] = 'Total question to choose';
$string['total_max_grade'] = 'Total Max Grade';
$string['err_sub_sum'] = 'Sub-questions points must be equal the question';
$string['err_integer'] = 'Must be an integer greater than 0 and less than Maximum grade';
$string['err_integer_number'] = 'Must be an integer greater than 0 and less than point';
$string['submit_form'] = 'Submit Form';
$string['save_form'] = 'Save Form';
$string['cancel_changes'] = 'Cancel changes?';
$string['error'] = 'Error';
$string['err_total_max_grade'] = 'Total Max Grade must not exceed Maximum grade';
$string['err_grading_method'] = 'Please select grading method';
$string['err_submit_modal'] = 'Save form with errors?';
$string['actions'] = 'Actions';
$string['add'] = 'Add';
$string['manage_comments'] = 'Manage Comments';
$string['grading_method_0'] = 'Choose…';
$string['grading_method_1'] = 'Accumulation of score';
$string['grading_method_2'] = 'Scoring lower';
$string['scoring_lower'] = 'Scoring lower -';
$string['total_q_groups_0'] = 'Choose…';
$string['total_q_groups'] = 'Selected {$a} groups';
$string['total_q_0'] = 'Choose…';
$string['total_q'] = '{$a} questions';
$string['number_of_subq_0'] = 'Please select number of sub questions';
$string['number_of_subq'] = 'Selected {$a} subquestions';
$string['saved_successfully'] = 'Saved successfully';
$string['must_be_number'] = 'Must be number more then 0';

// Comments
$string['accumulate_grade'] = 'Accumulate grade';
$string['view_comment_on_each_question'] = 'View the comment on each question';
$string['add_comment'] = 'Add comment';
$string['save'] = 'Save';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['are_you_sure'] = 'Are you sure you want to delete this comment?';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['global'] = 'GLOBAL';

// help icons
$string['maximum_grade_desc'] = 'Maximum score comes from the task definitions';
$string['total_questions_desc'] = 'Total test questions';
$string['total_questions_groups_desc'] = '
If there is a choice of questions, question sets must be defined, for each group you can define how many questions to answer. Each set of questions should have an equal score.';
$string['grading_methods_desc'] ='
Scoring: Score starts at 0 and points are added
Scoring lower: A score starts from the full score on the question and points are deducted
';
$string['question_desc'] = 'Number of question';
$string['sub_question_desc'] = 'Sections can be added to the question';
$string['max_points_desc'] = 'Maximum score on question and sections';
$string['bonus_desc'] = 'Bonus - is a type of question that completes the score with points';
$string['question_groups_desc'] = 'Assign a question to a group for selecting questions';
$string['actions_desc'] = 'Comments can be added to the question';
$string['info'] = 'Info';
$string['info_desc'] = 'Info description';

$string['draft_mode'] = 'Draft mode';
$string['ready_to_submit'] = 'Ready to submit';
$string['add_a_note'] = 'Add';

$string['absquestionsection'] = 'Absquestion settings';
$string['questionscolorheader'] = 'Colored question header';
$string['qtotalmax'] = 'Max number of questions';
$string['subqnummax'] = 'Max number of subquestions';

$string['freese_warning'] = 'There are submission for this assignment, the table cannot be modified';
$string['warning'] = 'Warning';
$string['warning_max_grade'] = 'The sum of comments grade is grater them max grade of the question';