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
 * Plugin capabilities are defined here.
 *
 * @package     quiz_advancedoverview
 * @category    access
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_advancedoverview;

use context_course;
use moodle_url;
use quiz_attempt;
use stdClass;
use core_user;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
require_once($CFG->dirroot . '/mod/quiz/report/advancedoverview/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

class quizdata {

    public $course;
    public $cm;
    public $groupid;
    public $questions;
    public $participants;
    public $participantsids;
    public $usersattempts;
    public $chartaverage;
    public $chartstate;
    public $chartgrade;
    public $enrolleduserscount;
    public $usersfinished;
    public $usersinprogress;
    public $usersnotstarted;
    public $usersfinishedlist;
    public $usersinprogresslist;
    public $usersnotstartedlist;
    public $quizobj;
    public $students;
    public $questionids;
    public $quiz;
    public $options;
    public $states;
    public $config;
    public $slots;
    public $openquestions = [];
    public $openquestionslist = [];
    public $anonymouscount = 1;

    public function __construct($cmid, $groupid = 0, $config = null) {

        $this->states = [];

        $defaultconfig = (object) [
                'anonymous_mode' => '0',
                'participants' => (object) [
                ],
        ];

        $this->config = $config ? $config : $defaultconfig;

        $this->config->anonymous_mode = isset($this->config->anonymous_mode) && $this->config->anonymous_mode == "1" ? 1 : 0;
        $this->config->participants->show_score =
                isset($this->config->participants->show_score) && $this->config->participants->show_score == "1" ? 1 : 0;
        $this->config->participants->full_view =
                isset($this->config->participants->full_view) && $this->config->participants->full_view == "0" ? 0 : 1;
        $this->config->participants->attempts_range =
                isset($this->config->participants->attempts_range) && count($this->config->participants->attempts_range) > 0 ?
                        $this->config->participants->attempts_range : [];
        $this->config->participants->userattemptssortby =
                isset($this->config->participants->userattemptssortby) && $this->config->participants->userattemptssortby != "" ?
                        $this->config->participants->userattemptssortby : 'attempt_number|desc';

        // Options.
        $this->prepare_options();

        list($this->course, $this->cm) = get_course_and_cm_from_cmid($cmid);
        $this->groupid = $groupid;

        $this->quizobj = \quiz::create($this->cm->instance);
        $this->quizobj->preload_questions();
        $this->quizobj->load_questions();

        list($this->participants, $this->participantsids) = $this->get_participants();

        $this->get_slots();

        $this->usersattempts = $this->get_attempts_per_users();

        list($this->enrolleduserscount,
                $this->usersfinished,
                $this->usersinprogress,
                $this->usersnotstarted,
                $this->usersfinishedlist,
                $this->usersinprogresslist,
                $this->usersnotstartedlist) = $this->quiz_submissions_stat();

    }

    public function get_slots() {
        global $DB;

        $slots = [];
        foreach ($DB->get_records('quiz_slots', ['quizid' => $this->cm->instance]) as $item) {
            $slots[] = $item->slot;
        }
        $this->slots = $slots;
    }

    public function prepare_options() {

        $this->options = [
                'anonymous_mode' => [
                        '0' => 0,
                        '1' => 1,
                ],
                'questions' => [
                        'options' => [
                                'option1' => 1,
                                'option2' => 2,
                        ],
                ],
                'participants' => [
                        'states' =>
                                [
                                        [
                                                'name' => 'all',
                                                'label' => get_string('all', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'inprogress',
                                                'label' => get_string('inprogress', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'notstarted',
                                                'label' => get_string('notstarted', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'finished',
                                                'label' => get_string('submitted', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'late',
                                                'label' => get_string('late', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                ],
                        'score_ranges' => [
                                [
                                        'name' => 'range-0',
                                        'label' => '0-55',
                                ],
                                [
                                        'name' => 'range-1',
                                        'label' => '55-60',
                                ],
                                [
                                        'name' => 'range-2',
                                        'label' => '60-70',
                                ],
                                [
                                        'name' => 'range-3',
                                        'label' => '70-80',
                                ],
                                [
                                        'name' => 'range-4',
                                        'label' => '80-90',
                                ],
                                [
                                        'name' => 'range-5',
                                        'label' => '90-100',
                                ],
                        ],
                        'search' => '',
                        'show_score' => [
                                '0' => 0,
                                '1' => 1,
                        ],
                ],
        ];
    }

    public function get_grade_range($grade) {
        foreach ($this->options['participants']['score_ranges'] as $key => $range) {
            $label = $range['label'];
            $minmax = explode("-", $label);
            $min = intval($minmax[0]);
            $max = intval($minmax[1]);
            if ($grade >= $min && $grade <= $max) {
                return $range['name'];
            }
        }
        return null;
    }

    public function prepare_charts() {
        $this->chartaverage = $this->calculate_grades();
        $this->chartstate = $this->get_chart_state_data();
        $this->chartgrade = $this->get_chart_grade_data();
    }

    public function prepare_questions() {
        $this->questions = $this->quizobj->get_questions();
    }

    public function prepare_students() {
        global $DB;

        $this->quiz = $DB->get_record('quiz', array('id' => $this->cm->instance), '*', MUST_EXIST);
        $this->questionids = array_keys($this->questions);
    }

    public function get_students_table() {
        $tabledata = [];

        foreach ($this->participants as $student) {

            $userattempts = quiz_get_user_attempts($this->cm->instance, $student->id, 'all', false);

            if (!$userattempts) {

                $attempt = new stdClass;
                $attempt->userid = $student->id;
                $attempt->state = null;
                $attempt->attempt = null;
                $attempt->sumgrades = null;
                $attempt->timestart = null;
                $attempt->timefinish = null;
                $attempt->id = null;

                $userattempts = [$attempt];

            }

            $userattemptsinfo = $this->table_data_user($userattempts, $student->id);

            $userattemptsinfo =
                    count($userattemptsinfo) > 1 ? $this->sort_table_data_user($userattemptsinfo, true) : $userattemptsinfo;

            $tabledata = array_merge($tabledata, $userattemptsinfo);
        }

        return $tabledata;
    }

    public function sort_table_data_user($data, $group = false) {
        $sortby = $this->config->participants->userattemptssortby;
        $sortbyparts = explode('|', $sortby);
        $sortfield = $sortbyparts[0];
        $sortdirection = $sortbyparts[1] ?? 'asc';

        // Sort the data.
        usort($data, function($a, $b) use ($sortfield, $sortdirection) {
            $aval = $a[$sortfield];
            $bval = $b[$sortfield];

            if ($sortdirection === 'desc') {
                return $bval <=> $aval;
            } else {
                return $aval <=> $bval;
            }
        });

        // Group the data if needed.
        if ($group) {
            $groupeddata = [];
            $maindata = true;
            $children = [];
            foreach ($data as $item) {
                if ($maindata) {
                    $groupeddata = $item;
                    $maindata = false;
                    $item['usermenubtn'] = $maindata;
                } else {
                    // Remove some info for nested rows.
                    $item['fullname'] = '';
                    $item['usermenubtn'] = $maindata;
                    $item['state'] = '';
                    $children[] = $item;
                }
            }

            $groupeddata['_children'] = $children;

            $data = $groupeddata;
        }

        return [$data];
    }

    public static function get_key_by_value($array, $property, $value) {
        foreach ($array as $key => $subarray) {
            foreach ($subarray as $subkey => $subvalue) {
                if ($subkey === $property && $subvalue === $value) {
                    return $key;
                }
            }
        }
        return null;
    }

    public static function get_item_with_max_value($array) {
        $maxvalue = 0;
        foreach ($array as $item => $data) {
            if ($maxvalue === null || $data->attempt > $maxvalue) {
                $maxvalue = $data->attempt;
            }
        }
        return $maxvalue;
    }

    public function table_data_user($attempts, $userid) {
        $data = [];

        $firstname = $this->participants[$userid]->firstname;
        $lastname = $this->participants[$userid]->lastname;
        $fullname = $firstname . ' ' . $lastname;

        $resetpasswordlink = $this->get_resetpassword_link($userid);
        $userprofilelink = $this->get_userprofile_link($userid);
        $loginaslink = $this->get_loginas_link($userid);
        $completereportlink = $this->get_completereport_link($userid);
        $outlinereportlink = $this->get_outlinereport_link($userid);

        $lastattempt = static::get_item_with_max_value($attempts);
        foreach ($attempts as $attempt) {

            $state = $attempt->state ?: 'notstarted';

            foreach ($this->options['participants']['states'] as $key => $item) {
                if ($item['name'] == $state) {
                    $this->options['participants']['states'][$key]['value']++;
                }
            }

            $continue = false;
            foreach ($this->config->participants->attempts_range as $k => $arange) {
                switch ($arange) {
                    case '1':
                        if ($attempt->attempt == 1) {
                            $continue = true;
                        }
                        break;
                    case '2':
                        if ($attempt->attempt == 2) {
                            $continue = true;
                        }
                        break;
                    case '3+':
                        if ($attempt->attempt >= 3) {
                            $continue = true;
                        }
                        break;
                    case 'last':
                        if ($lastattempt === $attempt->attempt) {
                            $continue = true;
                        }
                        break;
                }
            }

            if (!$continue && count($this->config->participants->attempts_range) > 0) {
                continue;
            }

            if (isset($this->config->participants->states) && !in_array($state, $this->config->participants->states) &&
                    !in_array('all', $this->config->participants->states)) {
                continue;
            }

            $attemptgrade = $attempt->sumgrades ? $attempt->sumgrades / $this->quiz->sumgrades * $this->quiz->grade : '0';

            $range = $this->get_grade_range($attemptgrade);

            if (isset($this->config->participants->score_ranges) && !in_array($range, $this->config->participants->score_ranges) &&
                    count($this->config->participants->score_ranges) > 0) {
                continue;
            }

            if (isset($this->config->participants->search) && $this->config->participants->search != '' &&
                    strpos(strtolower($fullname), strtolower($this->config->participants->search)) === false) {
                continue;
            }

            $attemptnumber = $attempt->attempt ?: '—';
            $starttime = $attempt->timestart ? userdate($attempt->timestart, '%d/%m/%y | %H:%M') : '—';
            $endtime = $attempt->timefinish ? userdate($attempt->timefinish, '%d/%m/%y | %H:%M') : '—';
            $duration =
                    ($attempt->timestart && $attempt->timefinish) ? format_time($attempt->timefinish - $attempt->timestart) : '—';

            $attempturl = $attempt->id ? new moodle_url('/mod/quiz/review.php', ['attempt' => $attempt->id]) : null;

            if ($attemptgrade != 0) {
                $attemptgradehtml = $attempturl ?
                        '<a target=`_blank` href=' . $attempturl->out(true) . '>' . round($attemptgrade, 2) . '</a>' :
                        round($attemptgrade, 2);
            } else {
                $attemptgradehtml = '—';
            }

            $rowdata = [
                    'checkbox' => '',
                    'attemptid' => $attempt->id,
                    'user_attempt_code' => $userid . '#' . $attemptnumber,
                    'userid' => $userid,
                    'fullname' => $attempturl ? '<a target=`_blank` href=' . $attempturl->out(true) . '>' . $fullname . '</a>' :
                            $fullname,
                    'firstname' => $this->participants[$userid]->firstname,
                    'lastname' => $this->participants[$userid]->lastname,
                    'usermenubtn' => '',
                    'state' => get_string($state, 'quiz_advancedoverview'),
                    'attempt_number' => $attemptnumber,
                    'grade' => $attemptgradehtml,
                    'starttime' => $starttime,
                    'endtime' => $endtime,
                    'duration' => $duration,
                    'resetpasswordlink' => $resetpasswordlink,
                    'userprofilelink' => $userprofilelink,
                    'loginaslink' => $loginaslink,
                    'completereportlink' => $completereportlink,
                    'outlinereportlink' => $outlinereportlink,
            ];

            if ($this->config->participants->full_view) {
                $att = $attempt->id ? quiz_attempt::create($attempt->id) : null;
                foreach ($this->questionids as $questionid) {
                    $question = $this->questions[$questionid];
                    $mark = $att ? $this->quiz_get_user_question_info($question, $att) : null;

                    $questionmaxgrade = $question->maxmark / $this->quiz->sumgrades * $this->quiz->grade;

                    $qindex = "Q " . $question->slot . " / " . round($questionmaxgrade);

                    $rowdata[$qindex] = $mark ?: '—';

                }
            }
            $data[] = $rowdata;
        }

        return $data;
    }

    public function quiz_get_user_question_info($question, $attempt) {
        $grade = $attempt->get_question_mark($question->slot) ?
                $attempt->get_question_mark($question->slot) * $this->quiz->grade / $this->quiz->sumgrades : 0;

        // Prepare questionlist requiresgrading.
        $this->add_to_openquestions($attempt, $question);

        $fullquestionstate = $this->icon_score($attempt, $question->slot, round($grade, 2));

        return $fullquestionstate;
    }

    private function add_to_openquestions($attempt, $question) {
        $qtypes = ['essay', 'opensheet', 'mlnlpessay'];
        if (in_array($question->qtype, $qtypes)) {
            $questionstateclass = $attempt->get_question_state_class($question->slot, true);
            if ($questionstateclass == 'requiresgrading') {
                if (!isset($this->openquestions[$question->id])) {
                    $this->openquestions[$question->id] = 1;
                } else {
                    $this->openquestions[$question->id]++;
                }
            }
        }
    }

    private function prepare_openquestions() {
        foreach ($this->openquestions as $key => $value) {
            $item = new stdClass;

            $link = new moodle_url('/mod/quiz/report.php', [
                    'id' => $this->cm->id,
                    'mode' => 'grading',
                    'slot' => $this->questions[$key]->slot,
                    'qid' => $this->questions[$key]->id,
                    'grade' => 'needsgrading'
            ]);

            $item->count_students = $value;
            $item->name = $this->questions[$key]->name;
            $item->link = $link->out(false);
            $item->qnumber = $this->questions[$key]->slot;

            $this->openquestionslist[] = $item;
        }
    }

    private function icon_score($attempt, $questionslot, $grade) {
        global $OUTPUT;

        $questionstateclass = $attempt->get_question_state_class($questionslot, true);

        $questionstate = $attempt->get_question_status($questionslot, true);

        $link = $attempt->get_attempt()->id ?
                new moodle_url('/mod/quiz/comment.php', ['attempt' => $attempt->get_attempt()->id, 'slot' => $questionslot]) : '';

        $data = new stdClass;
        $data->questionstateclass = $questionstateclass;

        if ($questionstateclass == 'notyetanswered') {
            $questionstate = '—';
        }

        $data->questionstate = $questionstate;
        $data->grade = $grade;
        $data->link = $link->out(false);

        $html = $OUTPUT->render_from_template('quiz_advancedoverview/gradeicon', $data);

        return $html;
    }

    private function get_participants() {
        global $DB;

        $params = $conditions = [];

        $sql = "
            SELECT u.* FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {user} u ON ue.userid = u.id
            JOIN {role_assignments} ra ON (u.id = ra.userid AND ra.roleid = :roleid)
            JOIN {context} c ON (ra.contextid = c.id AND c.instanceid = :instanceid)
            LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :cmid

        ";

        $params['instanceid'] = $this->course->id;
        $params['cmid'] = $this->cm->id;

        // Group.
        if ($this->groupid) {
            $sql .= " JOIN {groups_members} gm ON gm.userid = u.id ";
            $conditions[] = "gm.groupid = :groupid";
            $params['groupid'] = $this->groupid;
        }

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $params['roleid'] = $studentrole->id;

        $conditions[] = "e.courseid = :courseid";
        $params['courseid'] = $this->course->id;

        $conditions[] = "ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND " .
                "(ue.timeend = 0 OR ue.timeend > :now2) AND (quiza.preview = 0 OR quiza.preview IS NULL) AND u.deleted = 0 AND u.id <> '1' ";

        $params['now1'] = round(time(), -2);
        $params['now2'] = $params['now1'];
        $params['active'] = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;

        $participants = $DB->get_records_sql($sql . ' WHERE ' . implode(' AND ', $conditions), $params);

        if (empty($participants)) {
            return [[], []];
        }

        $tmpids = [];
        foreach ($participants as $user) {
            $tmpids[] = $user->id;
        }

        // Get roles.
        $sql = "
            SELECT ra.*, r.name, r.shortname, COUNT(ra.userid) as count
              FROM {role_assignments} ra, {role} r, {context} c
             WHERE ra.userid IN (" . implode(',', $tmpids) . ") 
                   AND ra.roleid = r.id
                   AND ra.contextid = c.id
                   AND ra.contextid = ?  
             GROUP BY ra.userid          
        ";

        $userids = [];
        $context = \context_course::instance($this->course->id);
        foreach ($DB->get_records_sql($sql, [$context->id]) as $item) {
            if ($item->count > 1) {
                unset($participants[$item->userid]);
            } else {
                $userids[] = $item->userid;
            }
        }

        // Anonymous mode.
        if ($this->config->anonymous_mode) {
            foreach ($participants as $key => $participant) {
                $participants[$key]->firstname = get_string('anon_user', 'quiz_advancedoverview') . ' ' . $this->anonymouscount;
                $participants[$key]->lastname = '';
                $this->anonymouscount++;
            }
        }

        return [$participants, $userids];
    }

    private function get_attempts_per_users() {
        global $DB;

        if (empty($this->participantsids)) {
            return [];
        }

        $sql = "
                SELECT
                DISTINCT CONCAT(u.id, '#', COALESCE(quiza.attempt, 0)) AS uniqueid,
                (CASE WHEN (quiza.state = 'finished' AND NOT EXISTS (
                                           SELECT 1 FROM {quiz_attempts} qa2
                                            WHERE qa2.quiz = quiza.quiz AND
                                                qa2.userid = quiza.userid AND
                                                 qa2.state = 'finished' AND (
                                COALESCE(qa2.sumgrades, 0) > COALESCE(quiza.sumgrades, 0) OR
                               (COALESCE(qa2.sumgrades, 0) = COALESCE(quiza.sumgrades, 0) AND qa2.attempt < quiza.attempt)
                                                ))) THEN 1 ELSE 0 END) AS gradedattempt,
                quiza.uniqueid AS usageid,
                quiza.id AS attempt,
                u.id AS userid,
                u.idnumber, u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.firstname,u.lastname,
                u.picture,
                u.imagealt,
                u.institution,
                u.department,
                u.email,
                quiza.state,
                quiza.sumgrades,
                quiza.timefinish,
                quiza.timestart,
                CASE WHEN quiza.timefinish = 0 THEN NULL
                     WHEN quiza.timefinish > quiza.timestart THEN quiza.timefinish - quiza.timestart
                     ELSE 0 END AS duration, COALESCE((
                                SELECT MAX(qqr.regraded)
                                  FROM {quiz_overview_regrades} qqr
                                 WHERE qqr.questionusageid = quiza.uniqueid
                          ), -1) AS regraded

                FROM  {user} u
                LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = ?
                JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = u.id
                JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = ?)

                WHERE u.id IN (" . implode(',', $this->participantsids) . ")

                ORDER BY quiza.id DESC
        ";

        $data = $DB->get_records_sql($sql, [$this->cm->instance, $this->course->id]);

        // Prepare questions per usageid.
        foreach ($data as $key => $item) {
            $item->questions_stat = $this->get_questions_per_usageid($item->usageid);
            $data[$key] = $item;
        }

        return $data;
    }

    private function get_questions_per_usageid($usageid = null) {
        global $DB;

        if (empty($this->slots) || $usageid == null) {
            return [];
        }

        $sql = "
            SELECT
                qas.id,
                qa.id AS questionattemptid,
                qa.questionusageid,
                qa.slot,
                qa.behaviour,
                qa.questionid,
                qa.variant,
                qa.maxmark,
                qa.minfraction,
                qa.maxfraction,
                qa.flagged,
                qa.questionsummary,
                qa.rightanswer,
                qa.responsesummary,
                qa.timemodified,
                qas.id AS attemptstepid,
                qas.sequencenumber,
                qas.state,
                qas.fraction,
                qas.timecreated,
                qas.userid

            FROM {question_attempts} qa
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
                    AND qas.sequencenumber = (
                            SELECT MAX(sequencenumber)
                            FROM {question_attempt_steps}
                            WHERE questionattemptid = qa.id
                        )

            WHERE qa.questionusageid = ? AND qa.slot IN (" . implode(',', $this->slots) . ")
        ";

        $data = $DB->get_records_sql($sql, [$usageid]);

        return $data;
    }

    private function get_chart_state_data() {

        // Encode data for js D3 pie chart.
        $data = [
                [
                        "label" => get_string('submitted', 'quiz_advancedoverview'),
                        "value" => $this->usersfinished,
                        "users" => $this->usersfinishedlist,
                ],
                [
                        "label" => get_string('notsubmitted', 'quiz_advancedoverview'),
                        "value" => $this->usersinprogress,
                        "users" => $this->usersinprogresslist,
                ],
                [
                        "label" => get_string('notstarted', 'quiz_advancedoverview'),
                        "value" => $this->usersnotstarted,
                        "users" => $this->usersnotstartedlist,
                ],
        ];
        return $data;
    }

    private function get_chart_grade_data() {
        global $DB;

        if ($this->quizobj->get_quiz()->grade == 100) {
            $scale = [
                    ['min' => 0, 'max' => 55],
                    ['min' => 55, 'max' => 60],
                    ['min' => 60, 'max' => 70],
                    ['min' => 70, 'max' => 80],
                    ['min' => 80, 'max' => 90],
                    ['min' => 90, 'max' => 100],
            ];
        } else {
            $scale = [
                    ['min' => 0, 'max' => 5],
                    ['min' => 5, 'max' => 6],
                    ['min' => 6, 'max' => 7],
                    ['min' => 7, 'max' => 8],
                    ['min' => 8, 'max' => 9],
                    ['min' => 9, 'max' => 10],
            ];
        }

        $labels = [];
        $labels[] = get_string('notsubmitted', 'quiz_advancedoverview');

        foreach ($scale as $item) {
            // If Hebrew.
            if (right_to_left()) {
                $labels[] = $item['max'] . ' - ' . $item['min'];
            } else {
                $labels[] = $item['min'] . ' - ' . $item['max'];
            }
        }

        if ($DB->record_exists('quiz_grades', ['quiz' => $this->quizobj->get_quiz()->id])) {

            list($bandsdata, $resultlist) = $this->quiz_advancedoverview_grade_bands($scale, $this->quizobj->get_quiz()->id,
                    $this->groupid, new \core\dml\sql_join(), $this->course->id);

            $notsubimtted = [$this->usersinprogress]; // Add users in progress / not submitted yet.
            $notsubimttedlist = [$this->usersinprogresslist]; // Add users in progress / not submitted yet.

            $chartdata = array_merge($notsubimtted, $bandsdata); // New merged array with data for chart.
            $chartdatausers = array_merge($notsubimttedlist, $resultlist); // New merged array with data for chart.

            $chart = self::get_chart($labels, $chartdata);
        } else {
            $chartdata = [0, 0, 0, 0, 0, 0, 0]; // Hardcoded value in case no one passed the quiz.
            $chartdatausers = [0, 0, 0, 0, 0, 0, 0];
        }

        // Encode data for js D3 pie chart.
        $data = array(
                array("key" => "keyname", "values" => array(
                        array(
                                "label" => $labels[0],
                                "value" => $chartdata[0],
                                "users" => $chartdatausers[0],
                        ),
                        array(
                                "label" => $labels[1],
                                "value" => $chartdata[1],
                                "users" => $chartdatausers[1],
                        ),
                        array(
                                "label" => $labels[2],
                                "value" => $chartdata[2],
                                "users" => $chartdatausers[2],
                        ),
                        array(
                                "label" => $labels[3],
                                "value" => $chartdata[3],
                                "users" => $chartdatausers[3],
                        ),
                        array(
                                "label" => $labels[4],
                                "value" => $chartdata[4],
                                "users" => $chartdatausers[4],
                        ),
                        array(
                                "label" => $labels[5],
                                "value" => $chartdata[5],
                                "users" => $chartdatausers[5],
                        ),
                        array(
                                "label" => $labels[6],
                                "value" => $chartdata[6],
                                "users" => $chartdatausers[6],
                        ),
                ),
                ),
        );

        return $data;
    }

    protected static function get_chart($labels, $data) {
        $chart = new \core\chart_bar();
        $chart->set_labels($labels);
        $chart->get_xaxis(0, true)->set_label(get_string('grade', 'core_grades'));

        $yaxis = $chart->get_yaxis(0, true);
        $yaxis->set_label(get_string('participants'));
        $yaxis->set_stepsize(max(1, round(max($data) / 10)));

        $series = new \core\chart_series(get_string('participants'), $data);
        $chart->add_series($series);
        return $chart;
    }

    protected function quiz_submissions_stat() {
        global $DB;

        $student = $DB->get_record('role', ['shortname' => 'student']);

        $params = [
                'quizid' => $this->quizobj->get_quiz()->id,
                'courseid' => $this->course->id,
                'roleid' => $student->id,
        ];

        // Count only one finished attempt for one user.
        if (!empty($this->participantsids)) {
            $data = $DB->get_records_sql("
                SELECT
                    userid,
                    max(id) AS id,
                    state,
                    quiz,
                    max(attempt) AS attempt
                FROM
                    {quiz_attempts}
                WHERE
                    userid IN (" . implode(',', $this->participantsids) . ")
                    AND quiz = :quizid
                    AND id IS NOT NULL
                    AND state = 'finished'
                GROUP BY
                    userid
                ",
                    $params);
        } else {
            $data = [];
        }

        $userskey = array_unique(array_keys($data));
        $usersfinishedlist = static::users_list_w_link_to_attempt($data);
        $usersfinished = count($userskey);

        if (!empty($this->participantsids)) {
            $data = $DB->get_records_sql("
                SELECT
                    userid,
                    max(id) AS id,
                    state,
                    quiz,
                    max(attempt) AS attempt
                FROM
                    {quiz_attempts}
                WHERE
                    userid IN (" . implode(',', $this->participantsids) . ")
                    AND quiz = :quizid
                    AND id IS NOT NULL
                    AND state = 'inprogress'
                GROUP BY
                    userid
                ",
                    $params);
        } else {
            $data = [];
        }

        $userskey = array_unique(array_keys($data));
        $usersinprogresslist = static::users_list_w_link_to_attempt($data);
        $usersinprogress = count($userskey);

        if (!empty($this->participantsids)) {
            $data = $DB->get_records_sql("
                SELECT u.id AS userid
                FROM {user} u            
                LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :quizid
                WHERE u.id IN (" . implode(',', $this->participantsids) . ") AND quiza.id IS NULL       
                ",
                    $params);
        } else {
            $data = [];
        }

        $userskey = array_unique(array_keys($data));
        $usersnotstartedlist = static::users_list_wo_link($userskey);
        $usersnotstarted = count($userskey);

        $enrolleduserscount = $usersfinished + $usersinprogress + $usersnotstarted;

        return [$enrolleduserscount, $usersfinished, $usersinprogress, $usersnotstarted, $usersfinishedlist, $usersinprogresslist,
                $usersnotstartedlist];
    }

    public function calculate_grades() {
        global $DB;

        if (!empty($this->participantsids)) {
            $params = [];
            $select =
                    "SELECT AVG(qa.sumgrades) AS averagegrade, MAX(qa.sumgrades) AS maxgrade, MIN(qa.sumgrades) AS mingrade, COUNT(qa.sumgrades) AS numgrades";
            $from = "FROM {quiz_attempts} qa JOIN {quiz} q ON qa.quiz = q.id";
            $where = "WHERE q.id = ? AND qa.userid IN (" . implode(',', $this->participantsids) .
                    ") AND qa.preview = 0 AND qa.state = 'finished'";
            $params[] = $this->quizobj->get_quiz()->id;

            if ($this->course) {
                $from .= " JOIN {course} c ON q.course = c.id";
                $where .= " AND c.id = ?";
                $params[] = $this->course->id;
            }

            $record = $DB->get_record_sql("$select $from $where", $params);
        } else {
            $record = new \StdClass();
            $record->numgrades = 0;
            $record->averagegrade = 0;
            $record->maxgrade = 0;
            $record->mingrade = 0;
        }

        if ($record->numgrades == 0) {
            $record->averagegrade = '-';
            $record->maxgrade = '-';
            $record->mingrade = '-';
        } else {
            $record->averagegrade = round(quiz_rescale_grade($record->averagegrade, $this->quizobj->get_quiz(), false), 1);
            $record->maxgrade = round(quiz_rescale_grade($record->maxgrade, $this->quizobj->get_quiz(), false));
            $record->mingrade = round(quiz_rescale_grade($record->mingrade, $this->quizobj->get_quiz(), false));
        }

        $record->str_max_grade = 'max_grade';
        $record->title_max_grade = get_string('max_grade', 'quiz_advancedoverview');

        $record->str_min_grade = 'min_grade';
        $record->title_min_grade = get_string('min_grade', 'quiz_advancedoverview');

        $record->str_attempts_grade = 'attempts';
        $record->title_attempts_grade = get_string('attempts', 'quiz_advancedoverview');

        return $record;
    }

    public function users_list_w_link($userids) {
        $list = [];

        foreach ($userids as $item) {
            $user = new stdClass;
            $user->firstname = $this->participants[$item]->firstname;
            $user->lastname = $this->participants[$item]->lastname;
            $user->disabled = false;
            $user->link = (new moodle_url('/user/profile.php', ['id' => $item]))->out();
            $list[] = $user;
        }

        return $list;
    }

    public function users_list_wo_link($userids) {
        $list = [];

        foreach ($userids as $item) {
            $user = new stdClass;
            $user->firstname = $this->participants[$item]->firstname;
            $user->lastname = $this->participants[$item]->lastname;
            $user->disabled = true;
            $user->link = '';
            $list[] = $user;
        }

        return $list;
    }

    public function users_list_w_link_to_attempt($usersattempts) {
        $list = [];

        foreach ($usersattempts as $uk => $ua) {
            $user = new stdClass;
            $user->firstname = $this->participants[$uk]->firstname;
            $user->lastname = $this->participants[$uk]->lastname;
            $user->link = (new moodle_url('/mod/quiz/review.php', ['attempt' => $ua->id]))->out();
            $list[] = $user;
        }

        return $list;
    }

    public function get_render_data() {

        $context = \context_module::instance($this->cm->id);
        $data = [
                'title' => format_string($this->cm->name, true, ['context' => $context]),
        ];

        // Build groups.
        $groups = [[
                'groupid' => '0',
                'groupname' => get_string('allparticipants', 'quiz_advancedoverview'),
        ]];

        foreach (groups_get_all_groups($this->course->id) as $group) {
            $groups[] = [
                    'groupid' => $group->id,
                    'groupname' => $group->name,
            ];
        }

        foreach ($groups as $key => $item) {
            $groups[$key]['selected'] = ($this->groupid == $item['groupid']) ? true : false;
        }

        $data['groups'] = $groups;

        // Buttons.
        $data['href_edit_question'] = new \moodle_url('/mod/quiz/edit.php', ['cmid' => $this->cm->id]);
        $data['href_preview_question'] =
                new \moodle_url('/mod/quiz/startattempt.php', ['cmid' => $this->cm->id, 'sesskey' => sesskey()]);

        // Build users and questions table.

        // Table according to questions.
        $tablequestion = [];
        foreach ($this->questions as $q) {

            $questionanswerder = $this->get_question_answered($q->id);
            $questionwrongs = $this->get_question_wrongs($q->id);
            $questionflags = $this->get_question_flags($q->id);
            $questionhints = $this->get_question_hints($q->id);
            $questionchats = $this->get_question_chats($q->id);

            $questiontitle = get_string('question');
            $qname = str_replace("'", '', $q->name);
            $url = quiz_advancedoverview_get_question_link($q, $this->cm->id);
            $questionlink = "<a class=d-flex target=_blank href=" . $url . "><span class=qname>" . $questiontitle . " " . $q->slot .
                    "</span><span class=description>" . $qname . "</span></a>"; // TODO: insert $url;
            $tablequestion[] = [
                    '#' => $q->slot,
                    $questiontitle => $questionlink,
                    get_string('answered', 'quiz_advancedoverview') => $questionanswerder,
                    get_string('wrong', 'quiz_advancedoverview') => $questionwrongs,
                    get_string('raiseflag', 'quiz_advancedoverview') => $questionflags,
                    get_string('usehint', 'quiz_advancedoverview') => $questionhints,
                    get_string('usechat', 'quiz_advancedoverview') => $questionchats,
            ];

        }

        $data['count_according_questions'] = count($tablequestion);
        $data['enable_table_according_questions'] = count($tablequestion) > 0 ? true : false;
        $data['data_table_according_questions'] = json_encode($tablequestion);

        // Students table.
        $data = array_merge($data, $this->get_render_students_data());

        $data['charts']['average'] = $this->chartaverage;
        $data['charts']['state'] = $this->chartstate;
        $data['charts']['grade'] = $this->chartgrade;

        $data['charts'] = json_encode($data['charts'], JSON_NUMERIC_CHECK);

        $data['charts_average_averagegrade'] = $this->chartaverage->averagegrade;
        $data['charts_average_maxgrade'] = $this->chartaverage->maxgrade;
        $data['charts_average_mingrade'] = $this->chartaverage->mingrade;

        $this->prepare_openquestions();

        $data['open_questions'] = $this->openquestionslist;
        $data['open_questions_count'] = count($this->openquestionslist);
        $data['enable_open_questions'] = count($this->openquestionslist) > 0 ? true : false;

        $data['options'] = json_encode($this->options);

        $data['cmid'] = $this->cm->id;
        $data['courseid'] = $this->course->id;
        $data['quizid'] = $this->quiz->id;
        $data['groupid'] = $this->groupid === null ? 0 : $this->groupid;

        $data['data_table_according_students_options'] = $this->options;

        $data['config'] = $this->config;

        return $data;
    }

    public function get_render_students_data() {

        $tablestudent = $this->get_students_table();

        $data['count_according_students'] = count($tablestudent);
        $data['enable_table_according_students'] = count($tablestudent) > 0 ? true : false;
        $data['data_table_according_students'] = json_encode($tablestudent);

        $allkey = static::get_key_by_value($this->options['participants']['states'], 'name', 'all');

        $this->options['participants']['states'][$allkey]['value'] = count($tablestudent);

        return $data;
    }

    private function generate_link($path, $params) {

        $url = new moodle_url($path, $params);

        return $url->out(false);
    }

    public function get_resetpassword_link($userid) {
        global $USER;

        if (
            (quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $userid))
            || 
            is_siteadmin()
        ) {
            $path = '/report/roster/resetpassword.php';
            $params = ['userid' => $userid, 'courseid' => $this->course->id, 'sesskey' => sesskey(), 'layout' => 'embedded'];

            return $this->generate_link($path, $params);
        }

        return '';
    }

    /**
     * Get the user profile link for a given user ID based on privileges.
     *
     * @param int $userid The user ID.
     * @return string The user profile link HTML or an empty string.
     */
    public function get_userprofile_link($userid) {
        global $USER;

        if (
            (quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $userid))
            || 
            is_siteadmin()
        ) {
            $path   = '/user/view.php';
            $params = ['id' => $userid, 'courseid' => $this->course->id];

            return $this->generate_link($path, $params);
        }

        return '';
    }

    public function get_loginas_link($userid) {
        global $USER;

        $coursecontext = context_course::instance($this->course->id);
        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() &&
                has_capability('moodle/user:loginas', $coursecontext) &&
                !quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $userid)
                || 
                is_siteadmin()) {
            $path = '/course/loginas.php';
            $params = ['user' => $userid, 'courseid' => $this->course->id, 'sesskey' => sesskey()];
        } else {
            return '';
        }

        return $this->generate_link($path, $params);
    }

    public function get_completereport_link($userid) {
        global $USER;

        if (
            (quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $userid))
            || 
            is_siteadmin()
        ) {
            $path = '/report/outline/user.php';
            $params = ['id' => $userid, 'course' => $this->course->id, 'mode' => 'complete'];
        } else {
            return '';
        }

        return $this->generate_link($path, $params);
    }

    public function get_outlinereport_link($userid) {
        global $USER;

        if (
            (quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !quiz_advancedoverview_is_user_have_course_update_privileges($this->course->id, $userid))
            || 
            is_siteadmin()
        ) {
            $path = '/report/outline/user.php';
            $params = ['id' => $userid, 'course' => $this->course->id, 'mode' => 'outline'];
        } else {
            return '';
        }

        return $this->generate_link($path, $params);
    }

    public function get_question_chats($questionid) {
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'advancedoverview', 'data');
        if (($result = $cache->get('chats')) === false) {
            return 0;
        } else {
            if (isset($result[$questionid])) {
                return $result[$questionid];
            } else {
                return 0;
            }
        }
    }

    public function get_question_hints($questionid) {
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'advancedoverview', 'data');
        if (($result = $cache->get('hints')) === false) {
            return 0;
        } else {
            if (isset($result[$questionid])) {
                return $result[$questionid];
            } else {
                return 0;
            }
        }
    }

    public function get_question_flags($questionid) {
        global $DB;

        // Get question attempts.
        $sql = "SELECT qa.*
            FROM {question_attempts} qa
            WHERE qa.questionid = :questionid";

        $params = [];
        $params['questionid'] = $questionid;

        $flagscount = 0;
        foreach ($DB->get_records_sql($sql, $params) as $key => $qa) {

            // Get flag for all attempts.
            if ($qa->flagged) {
                $flagscount++;
            }
        }

        return $flagscount;
    }

    public function get_question_wrongs($questionid) {
        global $DB;

        // Get question attempts.
        $sql = "SELECT COUNT(*) AS num_wrong
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
                WHERE qa.questionid = :questionid AND qas.state = 'gradedwrong'";

        $params = [];
        $params['questionid'] = $questionid;

        $questionattempts = $DB->get_record_sql($sql, $params);

        return $questionattempts->num_wrong;
    }

    public function get_question_answered($questionid) {
        global $DB;

        // Get question attempts.
        $sql = "SELECT COUNT(*) AS answeredcount
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
                WHERE qa.questionid = :questionid AND qas.state = 'complete'";

        $params = [];
        $params['questionid'] = $questionid;

        $questionattempts = $DB->get_record_sql($sql, $params);

        return $questionattempts->answeredcount;
    }

    public function quiz_advancedoverview_grade_bands($scale, $quizid, $currentgroup,
            \core\dml\sql_join $usersjoins = null, $courseid) {
        global $DB;

        if ($usersjoins && !empty($usersjoins->joins)) {
            $userjoin = " JOIN {user} u ON (u.id = qg.userid)
                        {$usersjoins->joins} ";
            $usertest = $usersjoins->wheres;
            $params = $usersjoins->params;
        } else {
            $userjoin = ' JOIN {user} u ON (u.id = qg.userid) ';
            $usertest = ' 1=1 ';
            $params = array();
        }

        if ($currentgroup > 0) {
            $sql = "
                SELECT UUID(), band, subquery.userid, subquery.uqaid
                FROM (
                    SELECT qg.grade AS band, u.id AS userid, uqa.id as uqaid
                    FROM {quiz_grades} AS qg
                    LEFT JOIN {groups_members} AS gm ON (qg.userid = gm.userid)
                    $userjoin
                    JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                    JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
                    LEFT JOIN (
                        SELECT
                            qa.userid,
                            max(qa.id) AS id,
                            qa.state,
                            qa.quiz,
                            max(qa.attempt) AS attempt
                        FROM
                            {quiz_attempts} qa
                        WHERE
                            qa.quiz = :quizid2
                            AND qa.id IS NOT NULL
                            AND qa.state = 'finished'
                        GROUP BY
                            qa.userid
                    ) uqa ON uqa.userid = u.id
                    WHERE $usertest AND qg.quiz = :quizid AND gm.groupid = :groupid AND u.suspended = 0 AND ue_d.status = 0
                        AND (
                            (ue_d.timestart = '0' AND ue_d.timeend = '0') OR
                            (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
                            (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                            (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                            )
                ) subquery
                ORDER BY band ";
        } else {
            $sql = "
                SELECT UUID(), band, subquery.userid, subquery.uqaid
                FROM (
                    SELECT qg.grade AS band, u.id AS userid, uqa.id as uqaid
                    FROM {quiz_grades} qg
                    $userjoin
                    JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                    JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
                    LEFT JOIN (
                        SELECT
                            qa.userid,
                            max(qa.id) AS id,
                            qa.state,
                            qa.quiz,
                            max(qa.attempt) AS attempt
                        FROM
                            {quiz_attempts} qa
                        WHERE
                            qa.quiz = :quizid2
                            AND qa.id IS NOT NULL
                            AND qa.state = 'finished'
                        GROUP BY
                            qa.userid
                    ) uqa ON uqa.userid = u.id
                    WHERE $usertest AND qg.quiz = :quizid AND u.suspended = 0 AND ue_d.status = 0
                        AND (
                            (ue_d.timestart = '0' AND ue_d.timeend = '0') OR
                            (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
                            (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                            (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                            )
                ) subquery
                ORDER BY band ";
        }

        $params['quizid'] = $quizid;
        $params['quizid2'] = $quizid;
        $params['groupid'] = $currentgroup;
        $params['courseid'] = $courseid;

        $data = $DB->get_records_sql_menu($sql, $params);
        $datalist = $DB->get_records_sql($sql, $params);

        $result = [];
        $resultlist = [];
        foreach ($scale as $item) {
            $count = 0;
            $list = [];
            foreach ($datalist as $key => $grade) {
                if ($grade->band >= $item['min'] && $grade->band < $item['max']) {
                    $count++;
                    $user = new stdClass;
                    $user->firstname = $this->participants[$datalist[$key]->userid]->firstname;
                    $user->lastname = $this->participants[$datalist[$key]->userid]->lastname;
                    $user->link = (new moodle_url('/mod/quiz/review.php', ['attempt' => $grade->uqaid]))->out();
                    $list[] = $user;
                }
                if (($item['max'] == 100 || $item['max'] == 10) && $grade->band == $item['max']) {
                    $count++;
                    $user = new stdClass;
                    $user->firstname = $this->participants[$datalist[$key]->userid]->firstname;
                    $user->lastname = $this->participants[$datalist[$key]->userid]->lastname;
                    $user->link = (new moodle_url('/mod/quiz/review.php', ['attempt' => $grade->uqaid]))->out();
                    $list[] = $user;
                }
            }

            $resultlist[] = $list;
            $result[] = $count;
        }

        return [$result, $resultlist];
    }

}
