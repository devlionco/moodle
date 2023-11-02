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
 * Diagnostic ADV question renderer class.
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for diagnostic ADV questions.
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadv_renderer extends qtype_renderer {
    private $context;
    private quiz_attempt $attemptobj;
    private $question;
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $OUTPUT;

        $this->question = $question = $qa->get_question();

        $currentanswer = $qa->get_last_qt_var('answer');
        $currentshortanswer = $qa->get_last_qt_var('customanswer');
        $currentcomment = $qa->get_last_qt_var('comment');

        $radioname = $qa->get_qt_field_name('answer');
        $inputname = $qa->get_qt_field_name('customanswer');
        $commentname = $qa->get_qt_field_name('comment');

        $resp = [
            'answer' => $currentanswer,
            'customanswer' => '',
            'comment' => $currentcomment
        ];
        if ($currentanswer == 'custom') {
            $resp['customanswer'] = $currentshortanswer;
        }

        if ($question->security) {
            $currentsecuritysure = $qa->get_last_qt_var('securitysure');
            $currentsecurity = $qa->get_last_qt_var('security');
            $securitysurename = $qa->get_qt_field_name('securitysure');
            $securityname = $qa->get_qt_field_name('security');
            $resp['security'] = $currentsecurity;
            $resp['securitysure'] = $currentsecuritysure;
        }

        $inputattributes = array(
            'type' => 'text',
            'name' => $inputname,
            'value' => $currentshortanswer,
            'data-answertype' => 'customtext',
            'id' => $inputname,
            'size' => 80,
            'disabled' => true,
            'class' => 'form-control d-inline',
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $feedbackimg = '';
        if ($options->correctness) {
            $resp = [
                'answer' => $currentanswer,
                'customanswer' => ''
            ];
            if ($currentanswer == 'custom') {
                $resp['customanswer'] = $currentshortanswer;
            }
            $answer = $question->get_matching_answer($resp);

            if ($answer) {
                $fraction = $answer->fraction;
            } else {
                $fraction = 0;
            }
            $inputattributes['class'] .= ' ' . $this->feedback_class($fraction);
            $feedbackimg = $this->feedback_image($fraction);
        }

        $questiontext = $question->format_questiontext($qa);

        $radioattributes = array(
            'type' => 'radio'
        );

        if ($options->readonly) {
            $radioattributes['disabled'] = 'disabled';
        }

        $hascustom = false;

        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $radiobuttons = array();
        $feedback = array();
        $classes = array();
        $counter = 0;

        foreach ($question->answers as $ans) {
            $ansid = $ans->id;
            $value = $ans->answer;
            if ($ans->custom) {
                $hascustom = true;
                continue;
            }
            $radioattributes['name'] = $radioname;
            $radioattributes['value'] = $ansid;
            $radioattributes['id'] = $ansid;
            $radioattributes['data-answertype'] = 'answer';
            $radioattributes['aria-labelledby'] = $radioattributes['id'] . '_label';

            $isselected = $currentanswer == $ansid;
            if ($isselected) {
                $radioattributes['checked'] = 'checked';
            } else {
                unset($radioattributes['checked']);
            }

            $choicetext = $question->format_text($ans->answer, $ans->answerformat, $qa, 'question', 'answer', $ansid);
            $choice = html_writer::div($choicetext, 'flex-fill ml-1');

            $radiobuttons[] = html_writer::empty_tag('input', $radioattributes) .
                html_writer::div($choice, 'd-flex w-auto', [
                    'id' => $radioattributes['id'] . '_label',
                    'data-region' => 'answer-label',
                ]);

            if ($options->feedback && empty($options->suppresschoicefeedback) &&
                $isselected && trim($ans->feedback)) {
                $feedback[] = html_writer::tag('div',
                    $question->make_html_inline($question->format_text(
                        $ans->feedback, $ans->feedbackformat,
                        $qa, 'question', 'answerfeedback', $ansid)),
                    array('class' => 'specificfeedback'));
            } else {
                $feedback[] = '';
            }

            $class = 'r' . ($counter % 2);
            $counter++;
            $classes[] = $lastclass = $class;
        }
        if ($hascustom) {
            $classes[] = 'ownanswer';
            $radioattributes['value'] = 'custom';
            $radioattributes['id'] = 'custom';
            $radioattributes['data-answertype'] = 'custom';
            $radioattributes['aria-labelledby'] = '0_label';
            $isselected = $currentanswer == 'custom';
            if ($isselected) {
                $radioattributes['checked'] = 'checked';
            } else {
                unset($radioattributes['checked']);
            }
            $choice = html_writer::div(get_string('customlabel', 'qtype_diagnosticadv'), 'flex-fill ml-1');

            $radiobuttons[] = html_writer::empty_tag('input', $radioattributes) .
                html_writer::div($choice, 'ownchoice', [
                    'id' => '0_label',
                    'data-region' => 'answer-label',
                ]) . html_writer::empty_tag('input', $inputattributes);
        }

        $result .= html_writer::start_tag('fieldset', array('class' => 'ablock no-overflow visual-scroll-x'));
        $result .= html_writer::start_tag('div', array('class' => 'answer'));

        foreach ($radiobuttons as $key => $radio) {
            $result .= html_writer::tag('div', $radio,
                    array('class' => $classes[$key])) . "\n";
        }

        $result .= html_writer::end_tag('div');// Answer.
        $result .= html_writer::end_tag('fieldset');// fieldset.

        $result .= html_writer::tag('p', get_string('commenthdr', 'qtype_diagnosticadv'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
        if ($options->readonly) {
            $result .= html_writer::tag('span', $currentcomment, ['class' => 'fst-italic']);
        } else {
            $result .= $this->output_editor($currentcomment, 'diagnosticadv_comment', $commentname);
        }

        $result .= html_writer::end_tag('div');

        if ($question->security) {
            $result .= html_writer::start_tag('fieldset', array('class' => 'ablock no-overflow visual-scroll-x'));
            $result .= html_writer::start_tag('div', array('class' => 'security'));
            $result .= html_writer::tag('p', get_string('securityhdr', 'qtype_diagnosticadv'));
            $radioattributes['name'] = $securitysurename;
            unset($radioattributes['data-answertype']);
            foreach (['yes', 'no'] as $value) {
                $radioattributes['value'] = $value;
                $radioattributes['id'] = 'securitysure' . $value;
                $radioattributes['aria-labelledby'] = $radioattributes['id'] . '_label';
                if ($currentsecuritysure == $value) {
                    $radioattributes['checked'] = 'checked';
                } else {
                    unset($radioattributes['checked']);
                }

                $choicetext = get_string('securitysure' . $value, 'qtype_diagnosticadv');
                $choice = html_writer::div($choicetext, 'flex-fill ml-1');
                $result .= html_writer::start_tag('div', ['class' => 'securitysurewrapper']);
                $result .= html_writer::empty_tag('input', $radioattributes) .
                    html_writer::div($choice, 'd-flex w-auto', [
                        'id' => 'securitysure' . $value . '_label',
                        'data-region' => 'answer-label',
                    ]);
                $result .= html_writer::end_tag('div');
            }
            $result .= html_writer::end_tag('div');// Answer.
            $result .= html_writer::end_tag('fieldset');// fieldset.

            $securitywrapperparams = ['class' => 'securitywrapper'];
            $disabledsecurity = false;
            if (isset($currentsecuritysure) && $currentsecuritysure == 'yes') {
                $securitywrapperparams['style'] = 'display:none';
                $disabledsecurity = true;
            }

            $result .= html_writer::start_tag('div', $securitywrapperparams);
            $result .= html_writer::tag('p', get_string('securitynohdr', 'qtype_diagnosticadv'));

            $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
            if ($options->readonly) {
                $result .= html_writer::tag('span', $currentsecurity, ['class' => 'fst-italic']);
            } else {
                $result .= $this->output_editor($currentsecurity, 'diagnosticadv_security', $securityname, $disabledsecurity);
            }

            $result .= html_writer::end_tag('div');

            $result .= html_writer::end_tag('div');
        }

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($resp),
                    array('class' => 'validationerror'));
        }

        if ($this->teacher_review($options)) {
            $data = $this->get_summary_data($qa->get_slot());

            $result .= $OUTPUT->render_from_template('qtype_diagnosticadv/summary', $data);
        }

        $this->page->requires->js_call_amd('qtype_diagnosticadv/answers', 'init', [$qa->get_outer_question_div_unique_id()]);

        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
        if (!$answer || !$answer->feedback) {
            return '';
        }

        return $question->format_text($answer->feedback, $answer->feedbackformat,
                $qa, 'question', 'answerfeedback', $answer->id);
    }

    public function correct_response(question_attempt $qa) {
        global $DB;

        $question = $qa->get_question();

        $answer = $question->get_matching_answer($question->get_correct_response());
        if (!$answer) {
            return '';
        }

        $correctanswer = s($question->clean_response($answer->answer));

        return get_string('correctansweris', 'qtype_diagnosticadv', $correctanswer);
    }

    /**
     * Returns an XHTML string for the editor
     *
     * @param string $data
     * @param string $query
     * @return string XHTML string for the editor
     */
    public function output_editor($data, $id, $name, $disabled = false) {
        $editor = editors_get_preferred_editor(FORMAT_HTML);
        $editor->set_text($data);
        $editor->use_editor($id, array('noclean'=>true));
        $params = ['id' => $id, 'name' => $name, 'rows' => 6];
        if ($disabled) {
            $params['disabled'] = true;
        }
        return html_writer::tag('textarea', $data, $params);
    }

    private function teacher_review(question_display_options $options) {
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $quizattemptid = optional_param('attempt', 0, PARAM_INT);

        $isreviewpage = $PAGE->url->compare(new moodle_url('/mod/quiz/review.php'), URL_MATCH_BASE);
        $isoverridepage = $PAGE->url->compare(new moodle_url('/mod/quiz/comment.php'), URL_MATCH_BASE);

        if ((!$isreviewpage && !$isoverridepage) || empty($quizattemptid) || !$options->readonly) {
            return false;
        }

        $this->attemptobj = quiz_attempt::create($quizattemptid);
        $cm = $this->attemptobj->get_cm();

        $this->context = \context_module::instance($cm->id);
        if (has_capability('mod/quiz:grade', $this->context)) {
            return true;
        }

        return false;
    }

    private function get_summary_data($slot) {
        global $DB, $SESSION;

        $data = [
            'answernumtotal' => '0'
        ];

        $summary = [];
        $comments = [];
        $security = [];
        $answers = [];

        $quizattempts = $this->get_quiz_attempts();
        $answeredcount = 0;
        $notansweredcount = 0;
        $totalcount = 0;
        $answers = $this->question->answers;
        foreach ($answers as $answer) {
            if (!$answer->custom) {
                $summary[$answer->answer] = [
                    'answer' => $answer->answer,
                    'answernum' => 0,
                    'answernumprc' => 0,
                    'answersured' => 0,
                    'answersuredprc' => 0,
                ];
            }
        }
        foreach ($quizattempts as $quizattempt) {
            $attemptobj = quiz_attempt::create($quizattempt->attemptid);
            $questionattempts = $attemptobj->all_question_attempts_originally_in_slot($slot);
            foreach ($questionattempts as $questionattempt) {
                $totalcount++;
                $attemptdata = $questionattempt->get_last_qt_data();

                if($questionattempt->get_state_class(false) == 'complete') {
                    $key = false;
                    if ($attemptdata['answer'] == 'custom') {
                        $key = $attemptdata['customanswer'];
                    } else {
                        $key = $answers[$attemptdata['answer']]->answer ?? false;
                    }

                    if ($key !== false) {
                        $answeredcount++;
                        if (!isset($summary[$key])) {
                            $summary[$key] = [
                                'answer' => $key,
                                'answernum' => 0,
                                'answernumprc' => 0,
                                'answersured' => 0,
                                'answersuredprc' => 0,
                                'comments' => [],
                                'security' => [],
                                'hascomments' => false
                            ];
                        }
                        $summary[$key]['answernum']++;
                        if ($attemptdata['securitysure'] == 'yes') {
                            $summary[$key]['answersured']++;
                        }

                        if ($this->question->anonymous) {
                            $user = get_string('anonymoususer', 'qtype_diagnosticadv', $answeredcount);
                        } else {
                            $user = \core_user::get_user($attemptobj->get_userid());
                            $user = html_writer::link(new \moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user));
                        }

                        $summary[$key]['comments'][] = [
                            'user' => $user,
                            'commenttext' => $attemptdata['comment'],
                            'securitytext' => $attemptdata['security']
                        ];

                        $summary[$key]['hascomments'] = true;
                    }
                } else {
                    $notansweredcount++;
                }
            }
        }

        foreach ($summary as $key => $summarydata) {
            $summary[$key]['answernumprc'] = number_format($summary[$key]['answernum'] / $answeredcount * 100, 0);
            $summary[$key]['answersuredprc'] = number_format($summary[$key]['answersured'] / $answeredcount * 100, 0);
        }

        $data['summary'] = array_values($summary);
        $data['hassummary'] = !empty($summary);
        $data['answernumtotal'] = $answeredcount;
        $data['notanswernumtotal'] = $notansweredcount;
        $data['notanswerprc'] = $totalcount > 0 ? number_format($notansweredcount / $totalcount * 100, 0) : 0;

        return $data;
    }

    /**
     * Return an array of quiz attempts, augmented by user idnumber.
     *
     * @return array of objects containing fields from quiz_attempts with user idnumber.
     */
    private function get_quiz_attempts() {
        global $DB;

        $groupstudentsjoins = get_enrolled_with_capabilities_join($this->context, '',
            ['mod/quiz:attempt', 'mod/quiz:reviewmyattempts']);
        $userfieldsapi = \core_user\fields::for_identity($this->context, true)
            ->with_name()->excluding('id', 'idnumber');

        $params = [
            'quizid' => $this->attemptobj->get_quizid(),
            'state' => 'finished',
        ];
        $fieldssql = $userfieldsapi->get_sql('u', true);
        $sql = "SELECT qa.id AS attemptid, qa.uniqueid, qa.attempt AS attemptnumber,
                       qa.quiz AS quizid, qa.layout, qa.userid, qa.timefinish,
                       qa.preview, qa.state, u.idnumber $fieldssql->selects
                  FROM {user} u
                  JOIN {quiz_attempts} qa ON u.id = qa.userid
                  {$groupstudentsjoins->joins}
                  {$fieldssql->joins}
                  WHERE {$groupstudentsjoins->wheres}
                    AND qa.quiz = :quizid
                    AND qa.state = :state
                  ORDER BY u.idnumber ASC, attemptid ASC";

        $params = array_merge($fieldssql->params, $groupstudentsjoins->params, $params);
        return $DB->get_records_sql($sql, $params);
    }
}
