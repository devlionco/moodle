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

namespace tool_question_reducer\tests;
use tool_question_reducer\dupe_merger\qcat_dupe_question_merger;
use tool_question_reducer\dupe_checker\question_dupe_checker;

defined('MOODLE_INTERNAL') || die();

class qcat_dupe_question_merger_test extends \advanced_testcase {

    protected function setUp() {
        $this->resetAfterTest();
        $this->questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $this->quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

    }

    public function create_duplicate_questions($qtype, $qcatid) {
        global $DB;
        $qa = $this->questiongenerator->create_question($qtype, null, array('category' => $qcatid));
        $qb = $this->questiongenerator->create_question($qtype, null, array('category' => $qcatid));
    }

    public function test_get_supported_question_types() {
        $expectedqtypes = array(
            'calculated',
            'calculatedsimple',
            'essay',
            'match',
            'multianswer',
            'multichoice',
            'numerical',
            'shortanswer',
            'truefalse',
        );

        $supportedqtypes = question_dupe_checker::get_supported_question_types();

        $this->assertEquals($expectedqtypes, $supportedqtypes);
    }

    public function test_merges_duplicate_questions_within_qcat() {
        global $DB;

        $supportedquestiontypes = question_dupe_checker::get_supported_question_types();
        foreach ($supportedquestiontypes as $qtype) {
            $cat = $this->questiongenerator->create_question_category();
            // This is a bit hacky, but calculatedsimple doesn't seem to work with the default $which.
            $which = null;
            if ($qtype == 'calculatedsimple') {
                $which = 'sumwithvariants';
            }
            $qa = $this->questiongenerator->create_question($qtype, $which, array('category' => $cat->id));
            $qb = $this->questiongenerator->create_question($qtype, $which, array('category' => $cat->id));

            qcat_dupe_question_merger::merge_duplicates($cat);

            $questioncount = $DB->count_records('question', array('category' => $cat->id, 'parent' => 0));
            $this->assertEquals(1, $questioncount, "Failed for {$qtype}");
        }
    }

    public function test_questions_that_share_quiz_are_not_merged() {
        global $SITE, $DB;
        $cat = $this->questiongenerator->create_question_category();
        $qa = $this->questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $qb = $this->questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $quiz = $this->quizgenerator->create_instance(array('course' => $SITE->id));
        quiz_add_quiz_question($qa->id, $quiz);
        quiz_add_quiz_question($qb->id, $quiz);

        qcat_dupe_question_merger::merge_duplicates($cat);

        $questioncount = $DB->count_records('question', array('category' => $cat->id));
        $this->assertEquals(2, $questioncount);
    }
}

