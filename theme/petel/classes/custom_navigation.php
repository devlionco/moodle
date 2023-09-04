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
        global $PAGE, $COURSE, $USER;

        if (is_siteadmin()) {
            return true;
        }

        $context = \context_course::instance($COURSE->id);
        $roles = get_user_roles($context, $USER->id);

        foreach ($roles as $role) {
            if (in_array($role->shortname, ['manager'])) {
                return true;
            }
        }

        // PTL-9713. PTL-9383.
        $coursecontext = \context_course::instance($COURSE->id);
        $notteacher    = !has_capability('moodle/course:update', $coursecontext);
        if ($notteacher && in_array($PAGE->pagetype, [
                        'mod-quiz-attempt',
                        'mod-quiz-review',
                        'mod-quiz-view',
                        'mod-quiz-report',
                        'mod-quiz-summary',
                        'mod-assign-view',
                ])) {
            $lists = $PAGE->secondarynav->get_children_key_list();
            if (isset($lists[0])) {
                foreach ($lists as $key) {
                    if ($key != $lists[0]) {
                        $PAGE->secondarynav->children->remove($key);
                    }
                }
            }
        }

        // PTL-9414.
        if (in_array($PAGE->pagetype,
            ['mod-quiz-view', 'mod-quiz-edit', 'mod-quiz-mod', 'mod-quiz-report', 'mod-quiz-attempt',
             'question-edit', 'mod-quiz-override', ''])) {
            $cmid = ($PAGE->cm->id) ?? optional_param('id', 0, PARAM_INT);
            if ($cmid) {
                $context = \context_module::instance($cmid);

                if (has_capability('mod/quiz:manage', $context)) {
                    $advancedoverviewurl = new \moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'advancedoverview'));
                    $PAGE->secondarynav->add(get_string('advancedoverviewlink', 'theme_petel'), $advancedoverviewurl);
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

            $lists = $PAGE->secondarynav->get_children_key_list();
            foreach ($exclude as $key) {
                if (in_array($key, $lists)) {
                    $PAGE->secondarynav->children->remove($key);
                }
            }
        }

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

        // PTL-9730.
        // Exclude links from menu (מורה צופה או כמורה עמית).
        $flagpermission = false;
        $rolespermitted = ['browsingteacher', 'teachercolleague', 'manager'];
        foreach ($roles as $role) {
            if (in_array($role->shortname, $rolespermitted)) {
                $flagpermission = true;
            }
        }

        if ($flagpermission) {
            $lists = $PAGE->secondarynav->get_children_key_list();
            if (isset($lists[0])) {
                foreach ($lists as $key) {
                    if ($key != $lists[0]) {
                        $PAGE->secondarynav->children->remove($key);
                    }
                }
            }
        }

        return true;
    }
}
