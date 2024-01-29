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

namespace theme_petel;

/**
 * Custom navigation.
 *
 * This class is copied and modified from /theme/boost/classes/boostnavbar.php
 *
 * @package    theme_petel
 * @copyright  2023 Luca Bösch <luca.boesch@bfh.ch>
 * @copyright  based on code from theme_boost by Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_navigation {

    /**
     * Custom secondary navigation.
     */
    public static function secondary_navigation(): bool {
        global $PAGE, $COURSE, $USER, $CFG;

        $coursecontext = \context_course::instance($COURSE->id);
        $roles = get_user_roles($coursecontext, $USER->id);

        // Add items.

        // Add advanced overview and grading students. PTL-9414.
        if (in_array($PAGE->pagetype, ['mod-quiz-view', 'mod-quiz-edit', 'mod-quiz-mod', 'mod-quiz-report', 'mod-quiz-attempt',
                                        'question-edit', 'mod-quiz-override', ''])) {

            if ($PAGE->pagetype == 'mod-quiz-report') {
                $mode = $cmid = required_param('mode', PARAM_RAW);
                switch ($mode) {
                    case 'advancedoverview':
                        $PAGE->set_secondary_active_tab('reportadvancedoverview');
                    case 'gradingstudents':
                        $PAGE->set_secondary_active_tab('reportgradingstudents');
                }
            }

            $cmid = ($PAGE->cm->id) ?? optional_param('id', 0, PARAM_INT);
            if ($cmid) {
                $context = \context_module::instance($cmid);

                $flagpermission = false;
                foreach ($roles as $role) {
                    if (in_array($role->shortname, ['teacher'])) {
                        $flagpermission = true;
                    }
                }

                if (has_capability('mod/quiz:manage', $context) || $flagpermission) {
                    $advancedoverviewurl = new \moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'advancedoverview'));
                    $PAGE->secondarynav->add(get_string('advancedoverviewlink', 'theme_petel'), $advancedoverviewurl,
                            $PAGE->secondarynav::TYPE_CUSTOM, 'reportadvancedoverview', 'reportadvancedoverview');

                    $gradingstudentsurl = new \moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'gradingstudents'));
                    $PAGE->secondarynav->add(get_string('gradingstudentslink', 'theme_petel'), $gradingstudentsurl,
                            $PAGE->secondarynav::TYPE_CUSTOM, 'reportgradingstudents', 'reportgradingstudents');

                     if(\community_oer\main_oer::is_activity_in_repository($cmid)){
                         $PAGE->secondarynav->children->remove('questionbank');
                         $PAGE->secondarynav->children->remove('mod_quiz_edit');
                     }
                }
            }
        }

        // Move items.

        // Move some menu items to "others" menu listbox for all users.
        $movetootherlist = [
                'quiz_report',
                'questionbank',
                'metadata'
        ];

        $lists = $PAGE->secondarynav->get_children_key_list();
        foreach ($movetootherlist as $key) {
            if (in_array($key, $lists)) {
                $PAGE->secondarynav->get($key)->set_force_into_more_menu(true);
            }
        }

        // If admin see all.
        if (is_siteadmin()) {
            return true;
        }

        foreach ($roles as $role) {
            if (in_array($role->shortname, ['manager'])) {
                return true;
            }
        }

        // Remove items.

        // PTL-10144. PTL-9713. PTL-9383. PTL-9730. Remove all not relevant items for students.
        // Exclude links from menu (מורה צופה או כמורה עמית).
        $flagpermission = false;
        $rolespermitted = ['browsingteacher', 'teachercolleague'];
        foreach ($roles as $role) {
            if (in_array($role->shortname, $rolespermitted)) {
                $flagpermission = true;
            }
        }

        $notteacher    = !has_capability('moodle/course:update', $coursecontext);

        if ($notteacher || $flagpermission) {
            $present = [];
            if (isset($lists[0])) {
                $present[] = $lists[0];
            }

            foreach ($roles as $role) {
                if (in_array($role->shortname, ['teacher']) && in_array('reportadvancedoverview', $lists)) {
                    $present[] = 'reportadvancedoverview';
                }
                if (in_array($role->shortname, ['teacher']) && in_array('reportgradingstudents', $lists)) {
                    $present[] = 'reportgradingstudents';
                }
            }

            // EC-330.
            require_once($CFG->dirroot.'/cohort/lib.php');
            $availabletocohort = get_config('community_sharecourse', 'availabletocohort');
            $flagcourse = cohort_is_member($availabletocohort, $USER->id) ? true : false;
            if(\community_oer\course_oer::funcs()::if_course_shared($COURSE->id) && $flagcourse && in_array('participants', $lists)) {
                $present[] = 'editsettings';
                $present[] = 'participants';
            }

            foreach ($PAGE->secondarynav->get_children_key_list() as $key) {
                if (!in_array($key, $present)) {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

        // PTL-9968.
        $flagpermission = false;
        foreach ($roles as $role) {
            if (in_array($role->shortname, ['teacher', 'student'])) {
                $flagpermission = true;
            }
        }

        if ($flagpermission) {
            foreach ($PAGE->secondarynav->get_children_key_list() as $key) {
                if ($key == 'competencies') {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

        // PTL-9609.
        // Exclude links from menu.
        if (!has_capability('moodle/site:config', \context_system::instance())) {
            $exclude = [
                    'filtermanagement',
                    'filtermanage',
                    'roleoverride',
                    'backup',
                    'restore',
                    'metadata',
                    //'questionbank',
                    'quiz_report',
            ];

            foreach ($exclude as $key) {
                if (in_array($key, $lists)) {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

        return true;
    }
}
