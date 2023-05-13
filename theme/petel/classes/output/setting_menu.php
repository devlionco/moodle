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
 * Setting menu.
 * @package     theme_petel
 @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use context_course;


/**
 * Setting menu class.
 * @package   theme_petel
 @copyright   2023 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_menu {

    /**
     *
     * @var int
     */
    private $categoryid = 0;

    /**
     *
     * @var arr
     */
    private $renderablelinks = array();

    /**
     *
     * @var bool
     */
    private $statusdefault = false;

    /**
     *
     * @var arr
     */
    private $linkskeys = array();

    /**
     * admin
     * admincategory
     * teacher
     * student
     * @var str
     */
    private $permission = '';

    /**
     *
     * @var str
     */
    private $typeurl = '';

    /**
     * Initiate instance.
     */
    public function __construct() {

        $this->get_permission();
        $this->get_type_url();

        if ($this->permission == 'admin' || empty($this->typeurl)) {
            $this->statusdefault = true;
            if (!empty($this->course_links())) {
                $this->renderablelinks = $this->course_links();
            }
            if (!empty($this->quiz_links())) {
                $this->renderablelinks = $this->quiz_links();
            }
        } else {
            switch ($this->typeurl) {
                case 'course_view':
                    $this->renderablelinks = $this->create_menu_by_user($this->course_links(), $this->course_defineded_links());
                    break;
                case 'quiz_view':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'quiz_edit':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'quiz_report':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'quiz_attempt':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'category_index':
                    $this->renderablelinks = $this->create_menu_by_user($this->category_links(), $this->category_defineded_links());
                    break;
                case 'user_index':
                    $this->renderablelinks = $this->create_menu_by_user($this->user_links(), $this->user_defineded_links());
                    break;
                case 'questionnaire':
                    $this->statusdefault = true;
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->questionnaire_defineded_links());
                    break;
            }
        }
    }

    /**
     * Get perrmission.
     * @return string
     */
    public function get_permission() {
        global $COURSE, $USER;

        if ($COURSE->category) {
            $this->categoryid = $COURSE->category;
        } else {
            $this->categoryid = optional_param('categoryid', '0', PARAM_INT);
        }

        // Is permission admin.
        if (is_siteadmin()) {
            $this->permission = 'admin';
            return 'admin';
        }

        // Is permission category.
        if (can_edit_in_category($this->categoryid)) {
            $this->permission = 'admincategory';
            return 'admincategory';
        }

        $context = context_course::instance($COURSE->id);

        // Is permission teacher.
        if (has_capability('moodle/course:update', $context)) {
            $this->permission = 'teacher';
            return 'teacher';
        }

        // Check role juniorteacher.
        $flag = false;
        $roles = get_user_roles($context, $USER->id, true);
        foreach($roles as $role){
            if($role->shortname == 'juniorteacher'){
                $flag = true;
            }
        }

        if($flag){
            $this->permission = 'juniorteacher';
            return 'juniorteacher';
        }

        $this->permission = 'student';
        return 'student';
    }

    public function get_linkskeys() {
        return $this->linkskeys;
    }

    /**
     * Get type url.
     * @return string
     */
    public function get_type_url() {
        global $PAGE;

        if (strpos($PAGE->url, '/course/view.php')) {
            $this->typeurl = 'course_view';
            return 'course_view';
        }

        if (strpos($PAGE->url, '/mod/quiz/view.php')) {
            $this->typeurl = 'quiz_view';
            return 'quiz_view';
        }

        if (strpos($PAGE->url, '/mod/quiz/edit.php')) {
            $this->typeurl = 'quiz_edit';
            return 'quiz_edit';
        }

        if (strpos($PAGE->url, '/mod/quiz/report.php')) {
            $this->typeurl = 'quiz_report';
            return 'quiz_report';
        }

        if (strpos($PAGE->url, '/mod/quiz/attempt.php')) {
            $this->typeurl = 'quiz_attempt';
            return 'quiz_attempt';
        }

        if (strpos($PAGE->url, '/course/index.php')) {
            $this->typeurl = 'category_index';
            return 'category_index';
        }

        if (strpos($PAGE->url, '/user/index.php')) {
            $this->typeurl = 'user_index';
            return 'user_index';
        }

        if (strpos($PAGE->url, 'mod/questionnaire')) {
            $this->typeurl = 'questionnaire';
            return 'questionnaire';
        }

        return '';
    }

    /**
     * Create menu.
     * @param array $alllinks
     * @param array $define
     * @return array
     */
    public function create_menu_by_user($alllinks, $define) {
        $result = array();
        if(!empty($define[$this->permission])){
            if (isset($define[$this->permission][$this->typeurl])) {
                $arr = $define[$this->permission][$this->typeurl];
            } else {
                $arr = $define[$this->permission];
            }
            // TODO: Figure this menu out, hack by nadav
            $arr_items = array();
            if (is_array(reset($arr))) {
                foreach ($arr as $is_array) {
                    if (is_array($is_array)) {
                        $arr_items = array_merge($arr_items, $is_array);
                    }
                }
                $arr_items = array_unique($arr_items);
            } else {
                $arr_items = $arr;
            }
            foreach($arr_items as $item) {
                if (isset($alllinks[$item])) {
                    $result[] = $alllinks[$item];
                }
            }
        }
        return $result;
    }

    /**
     * Get menu
     * @return \action_menu
     */
    public function get_menu() {
        $menu = new action_menu();
        if (!empty($this->renderablelinks)) {
            foreach ($this->renderablelinks as $item) {
                $link = $this->create_link($item);
                $menu->add_secondary_action($link);
                $menu->set_owner_selector('editsettings');
            }
        }
        return $menu;
    }

    /**
     * Create link
     * @param array $link
     * @return \action_link
     */
    private function create_link($link) {

        $attributes = array(
                'class' => isset($link['class'])?$link['class']:""
        );

        return new action_link($link['url'], $link['text'], null, $attributes, new pix_icon($link['icon'], $link['text']));
    }

    /**
     * Return action name
     * @param object $action
     * @return string
     */
    private function unique_action_name($action) {
        global $CFG;

        $str = str_replace($CFG->wwwroot, '', $action->url->__toString());
        $ar = explode('?', $str);

        $path = str_replace(array('.php'), '', $ar[0]);
        $path = str_replace(array('/'), '-', $path);
        $path = ltrim($path, '-');

        // Check params.
        $arr = array();
        foreach ($action->url->params() as $key => $value) {
            if (is_numeric($value) || $value == sesskey()) {
                continue;
            }
            $arr[] = $key;
            $arr[] = $value;
        }

        $params = implode('-', $arr);
        if (!empty($params)) {
            $params = '-' . $params;
        }

        $name = $path . $params;
        return $name;
    }

    /**
     * Return course links
     * @return array
     */
    private function course_links() {
        global $CFG, $PAGE, $USER, $COURSE, $DB;

        $generatemenu = array();
        $basicmenu = $this->get_header_settings_menu();

        if (!empty($basicmenu->get_secondary_actions())) {
            foreach ($basicmenu->get_secondary_actions() as $key => $action) {
                $tmp = array();
                $tmp['text'] = $action->text;
                $tmp['url'] = $action->url;
                $tmp['icon'] = $action->icon->pix;

                $tmp['class'] = '';
                if(isset($action->attributes['class']) && !empty($action->attributes['class'])){
                    $tmp['class'] = preg_replace('/(^|\s+)icon(\s+|$)/i', '', $action->attributes['class']);
                }

                $name = $this->unique_action_name($action);
                if (isset($generatemenu[$name])) {
                    $name = $name . '-repeat';
                }
                $generatemenu[$name] = $tmp;
            }

            if ($this->statusdefault) {
                return $generatemenu;
            }

            $addelements = array(
                'user-index' => array(
                    'text' => get_string('participants'),
                    'url' => new moodle_url('/user/index.php', array('id' => $PAGE->course->id)),
                    'icon' => 'i/users'
                ),
                'grade-report-index' => array(
                    'text' => get_string('grades'),
                    'url' => new moodle_url('/grade/report/index.php', array('id' => $PAGE->course->id)),
                    'icon' => 'i/report'
                ),
            );

            $generatemenu = array_merge($generatemenu, $addelements);
        }

        // Student.
        if ($this->permission == 'student' && $this->typeurl == 'course_view') {
            $addelements = array(
                'grade-report-index' => array(
                    'text' => get_string('grades'),
                    'url' => new moodle_url('/grade/report/index.php', array('id' => $PAGE->course->id)),
                    'icon' => 'i/report'
                ),
            );

            $generatemenu = array_merge($generatemenu, $addelements);
        }
        return $generatemenu;
    }

    /**
     * return links
     * @return array
     */
    private function get_instance_links() {
        global $CFG, $PAGE, $USER, $COURSE;

        $generatemenu = array();
        $basicmenu = $this->get_header_region_settings_menu();

        // PTL-6366.
        $secondaryactions = [];
        $node = $PAGE->navigation->find_active_node();
        foreach ($basicmenu->get_secondary_actions() as $action) {
            if($node->type == navigation_node::TYPE_ACTIVITY && \community_oer\main_oer::if_activity_in_research_mode($node->key)){
                if(!in_array($action->url->get_path(), ['/mod/quiz/edit.php', '/question/edit.php'])){
                    $secondaryactions[] = $action;
                }
            }else{
                $secondaryactions[] = $action;
            }
        }

        if (!empty($secondaryactions)) {
            foreach ($secondaryactions as $key => $action) {
                $tmp = array();
                $tmp['text'] = $action->text;
                $tmp['url'] = $action->url;
                //TODO IMPROVE ICON FOR CUSTOM VIEW
                if ($action->icon->pix == 'icon') {
                    $tmp['icon'] = "i/navigationitem";
                } else {
                    $tmp['icon'] = $action->icon->pix;
                }

                $name = $this->unique_action_name($action);
                if (isset($generatemenu[$name])) {
                    $name = $name . '-repeat';
                }
                $generatemenu[$name] = $tmp;
            }

            if ($this->statusdefault) {
                return $generatemenu;
            }

            $addelements = array();

            $generatemenu = array_merge($generatemenu, $addelements);
        }

        return $generatemenu;
    }

    /**
     * Return quiz links
     * @return array
     */
    private function quiz_links() {
        return $this->get_instance_links();
    }

    /**
     * Return category links
     * @return array
     */
    private function category_links() {
        return $this->get_instance_links();
    }

    /**
     * Return user links
     * @return array
     */
    private function user_links() {
        return $this->get_instance_links();
    }

    /**
     * Return course defined links
     * @return array
     */
    private function course_defineded_links() {
        return array(
            'admincategory' => array(
                'course-edit',
                'course-view-edit-on',
                'course-completion',
                'enrol-manual-unenrolself',
                'filter-manage',
                'grade-edit-tree-index',
                'backup-backup',
                'backup-restorefile',
                'backup-import',
                'course-publish-index',
                'course-reset',
                'course-admin',
                'Javascript:--void(0)' // Copy course to category
            ),
            'teacher' => array(
                'course-edit',
                'user-index',
                'grade-report-index',
                'admin-tool-recyclebin-index',
                'duplicate-index',
                'Javascript:--void(0)', // Copy course to category
                'course-admin',
            ),
            'juniorteacher' => array(
                    'course-edit',
                    'user-index',
                    'grade-report-index',
                    'admin-tool-recyclebin-index',
                    'duplicate-index',
                    'Javascript:--void(0)', // Copy course to category
            ),
            'student' => array(
                'grade-report-index'
            )
        );
    }

    /**
     * Return quiz defined links
     * @return array
     */
    private function quiz_defineded_links() {
        return array(
            'admincategory' => array(
                'course-modedit',
                'mod-quiz-overrides-mode-group',
                'mod-quiz-overrides-mode-user',
                'mod-quiz-edit',
                'mod-quiz-startattempt',
                'mod-quiz-report-mode-overview',
                'mod-quiz-report-mode-teacheroverview',
                'mod-quiz-report-mode-correctanswer',
                'mod-quiz-report-mode-correctanswer-answers-no',
                'mod-quiz-report-mode-responses',
                'mod-quiz-report-mode-statistics',
                'mod-quiz-report-mode-grading',
                'local-estimate-setestimatequiz',
                'admin-roles-assign',
                'admin-roles-permissions',
                'admin-roles-check',
                'filter-manage',
                'report-log-index',
                'backup-backup',
                'backup-restorefile',
                'question-edit',
                'question-edit-repeat',
                'question-category',
                'question-import',
                'question-export',
                'local-purgequestioncategory-category',
                'local-metadata-index-action-moduledata',
                'local-weizman_essay_question-showtable',
                'local-weizman_export_quiz-download_report_quiz'
            ),
            'teacher' => array(
                'quiz_view' => array(
                    'course-modedit',
                    'mod-quiz-overrides-mode-group',
                    'mod-quiz-overrides-mode-user',
                    'mod-quiz-edit',
                    'mod-quiz-startattempt',
                    'mod-quiz-report-mode-teacheroverview',
                    'mod-quiz-report-mode-correctanswer',
                    'mod-quiz-report-mode-correctanswer-answers-no',
                    'mod-quiz-report-mode-responses',
                    'mod-quiz-report-mode-statistics',
                    'mod-quiz-report-mode-grading',
                    'local-weizman_essay_question-showtable',
                    'local-weizman_export_quiz-download_report_quiz',
                    'question-edit'
                ),
                'quiz_report' => array(
                    'course-modedit',
                    'mod-quiz-overrides-mode-group',
                    'mod-quiz-overrides-mode-user',
                    'mod-quiz-edit',
                    'mod-quiz-startattempt',
                    'mod-quiz-report-mode-teacheroverview',
                    'mod-quiz-report-mode-correctanswer',
                    'mod-quiz-report-mode-correctanswer-answers-no',
                    'mod-quiz-report-mode-responses',
                    'mod-quiz-report-mode-statistics',
                    'mod-quiz-report-mode-grading',
                    'admin-roles-assign',
                    'local-weizman_essay_question-showtable',
                    'local-weizman_export_quiz-download_report_quiz'
                ),
                'quiz_attempt' => array(
                    'course-modedit',
                    'mod-quiz-overrides-mode-group',
                    'mod-quiz-overrides-mode-user',
                    'mod-quiz-edit',
                    'mod-quiz-startattempt',
                    'mod-quiz-report-mode-teacheroverview',
                    'mod-quiz-report-mode-correctanswer',
                    'mod-quiz-report-mode-correctanswer-answers-no',
                    'mod-quiz-report-mode-responses',
                    'mod-quiz-report-mode-statistics',
                    'mod-quiz-report-mode-grading',
                    'admin-roles-assign',
                    'local-weizman_essay_question-showtable',
                    'local-weizman_export_quiz-download_report_quiz'
                )
            ),
            'juniorteacher' => array(
                    'quiz_view' => array(
                            'course-modedit',
                            'mod-quiz-overrides-mode-group',
                            'mod-quiz-overrides-mode-user',
                            'mod-quiz-edit',
                            'mod-quiz-startattempt',
                            'mod-quiz-report-mode-teacheroverview',
                            'mod-quiz-report-mode-correctanswer',
                            'mod-quiz-report-mode-correctanswer-answers-no',
                            'mod-quiz-report-mode-responses',
                            'mod-quiz-report-mode-statistics',
                            'mod-quiz-report-mode-grading',
                            'local-weizman_essay_question-showtable',
                            'local-weizman_export_quiz-download_report_quiz',
                            'question-edit',
                    ),
                    'quiz_report' => array(
                            'course-modedit',
                            'mod-quiz-overrides-mode-group',
                            'mod-quiz-overrides-mode-user',
                            'mod-quiz-edit',
                            'mod-quiz-startattempt',
                            'mod-quiz-report-mode-teacheroverview',
                            'mod-quiz-report-mode-correctanswer',
                            'mod-quiz-report-mode-correctanswer-answers-no',
                            'mod-quiz-report-mode-responses',
                            'mod-quiz-report-mode-statistics',
                            'mod-quiz-report-mode-grading',
                            'admin-roles-assign',
                            'local-weizman_essay_question-showtable',
                            'local-weizman_export_quiz-download_report_quiz'
                    ),
                    'quiz_attempt' => array(
                            'course-modedit',
                            'mod-quiz-overrides-mode-group',
                            'mod-quiz-overrides-mode-user',
                            'mod-quiz-edit',
                            'mod-quiz-startattempt',
                            'mod-quiz-report-mode-teacheroverview',
                            'mod-quiz-report-mode-correctanswer',
                            'mod-quiz-report-mode-correctanswer-answers-no',
                            'mod-quiz-report-mode-responses',
                            'mod-quiz-report-mode-statistics',
                            'mod-quiz-report-mode-grading',
                            'admin-roles-assign',
                            'local-weizman_essay_question-showtable',
                            'local-weizman_export_quiz-download_report_quiz'
                    )
            ),
            'student' => array(
                'quiz_view' => array(
                ),
                'quiz_report' => array(
                ),
                'quiz_attempt' => array(
                )
            )
        );
    }

    /**
     * Return quiz defined links
     *
     * @return array
     */
    private function questionnaire_defineded_links() {
        return array(
                'admincategory' => array(),
                'teacher' => array(
                        'course-modedit',
                        'mod-questionnaire-qsettings',
                        'mod-questionnaire-questions',
                        'mod-questionnaire-feedback',
                        'mod-questionnaire-preview',
                        'mod-questionnaire-report-action-vall',
                        'mod-questionnaire-report-action-vall-repeat',
                        'mod-questionnaire-report-action-vallasort',
                        'mod-questionnaire-report-action-vallarsort',
                        'questionnaire-report-action-delallresp',
                        'mod-questionnaire-report-action-dwnpg',
                        'mod-questionnaire-report-action-vresp',
                        'mod-questionnaire-report-action-vresp-repeat',
                        'mod-questionnaire-show_nonrespondents',

                ),
                'juniorteacher' => array(
                        'course-modedit',
                        'mod-questionnaire-qsettings',
                        'mod-questionnaire-questions',
                        'mod-questionnaire-feedback',
                        'mod-questionnaire-preview',
                        'mod-questionnaire-report-action-vall',
                        'mod-questionnaire-report-action-vall-repeat',
                        'mod-questionnaire-report-action-vallasort',
                        'mod-questionnaire-report-action-vallarsort',
                        'questionnaire-report-action-delallresp',
                        'mod-questionnaire-report-action-dwnpg',
                        'mod-questionnaire-report-action-vresp',
                        'mod-questionnaire-report-action-vresp-repeat',
                        'mod-questionnaire-show_nonrespondents',

                ),
                'student' => array()
        );
    }

    /**
     * Return category defined links
     * @return arr
     */
    private function category_defineded_links() {
        return array(
            'admincategory' => array(
                'course-management',
            ),
            'teacher' => array(
            ),
            'juniorteacher' => array(
            ),
            'student' => array(
            )
        );
    }

    /**
     * Return user defined links
     * @return arr
     */
    private function user_defineded_links() {
        return array(
            'admincategory' => array(
                'enrol-users',
                'enrol-instances',
                'enrol-editinstance-type-manual',
                'group-index',
                'admin-roles-permissions',
                'admin-roles-check',
                'enrol-otherusers',
                'blocks-configurable_reports-viewreport',
            ),
            'teacher' => array(
                'enrol-users',
                'enrol-instances',
                'group-index',
            ),
            'juniorteacher' => array(
                    'enrol-users',
                    'enrol-instances',
                    'group-index',
            ),
            'student' => array(
            )
        );
    }

    /**
     * Get region settings menu
     * @return \action_menu
     */
    public function get_header_region_settings_menu() {
        global $CFG, $PAGE;

        $context = $PAGE->context;
        $menu = new action_menu();

        if ($context->contextlevel == CONTEXT_MODULE) {

            $PAGE->navigation->initialise();
            $node = $PAGE->navigation->find_active_node();
            $buildmenu = false;
            // If the settings menu has been forced then show the menu.
            if ($PAGE->is_settings_menu_forced()) {
                $buildmenu = true;
            } else if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                    $node->type == navigation_node::TYPE_RESOURCE)) {

                $items = $PAGE->navbar->get_items();
                $navbarnode = end($items);
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                // TODO improve menu views for the next versions.
                if ($navbarnode->key == 'quiz_report_overview' || $navbarnode->key == 'quiz_report_teacheroverview' ||
                    $navbarnode->key == 'mod_quiz_edit' || $navbarnode->key == 'mod_quiz_preview' ||
                    $navbarnode->key == 'quiz_report_grading') {
                    $buildmenu = true;
                } else if ($navbarnode && ($navbarnode->key === $node->key && $navbarnode->type == $node->type)) {
                    $buildmenu = true;
                }
            }
            if ($buildmenu) {
                // Get the course admin node from the settings navigation.
                $node = $PAGE->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }
        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($PAGE->pagetype === 'course-index-category') {
                $node = $PAGE->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }
        } else {
            $items = $PAGE->navbar->get_items();
            $navbarnode = end($items);

            if ($navbarnode && ($navbarnode->key === 'participants')) {
                $node = $PAGE->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }
        }
        return $menu;
    }

    /**
     * Get settings menu
     * @return \action_menu
     */
    public function get_header_settings_menu() {
        global $CFG, $PAGE;

        $context = $PAGE->context;
        $menu = new action_menu();

        $items = $PAGE->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($PAGE->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
                !$courseformat->has_view_page()) {

            $PAGE->navigation->initialise();
            $activenode = $PAGE->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($PAGE->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                    $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
                !empty($currentnode) &&
                $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
                !empty($currentnode) &&
                ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $PAGE->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $PAGE->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $PAGE->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $PAGE->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $menu;
    }

    /**
     * Build action menu
     * @param action_menu $menu
     * @param navigation_node $node
     * @param bool $indent
     * @param bool $onlytopleafnodes
     * @return bool
     */
    private function build_action_menu_from_navigation(action_menu $menu,
            navigation_node $node,
            $indent = false,
            $onlytopleafnodes = false) {
        $skipped = false;
        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {
            if ($menuitem->display) {
                if ($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if ($menuitem->action) {
                    if ($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if (!empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }

                    $this->linkskeys[] = $menuitem->key;
                } else {
                    if ($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);

                    $this->linkskeys[] = $menuitem->key;
                }
                if ($indent) {
                    $link->add_class('m-l-1');
                }
                if (!empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }
        }
        return $skipped;
    }
}

