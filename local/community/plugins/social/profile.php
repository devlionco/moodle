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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    community_social
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/community/plugins/social/classes/event/social_profile_view.php');

require_login();
$PAGE->set_context(context_system::instance());

$navdraweropen = get_user_preferences('drawer-open-nav') == 'true' ? "true" : "false";
set_user_preference('drawer-open-nav', "false");

$strname = get_string('pluginname', 'community_social');
$PAGE->set_url('/local/community/plugins/social/profile.php', array('id' => $USER->id));
$PAGE->set_title($strname);

// Check if user active.
$isvisited = get_user_preferences('community_social_enable');
$userid = optional_param('id', null, PARAM_INT);

if ($isvisited != 1) {
    $urltogo = new moodle_url('/local/community/plugins/social/index.php',
            array_filter(array('id' => $userid), 'social_filter_userid'));
    redirect($urltogo);
}

$data = array(
        'social_enable' => new moodle_url('/local/community/plugins/social/index.php',
                array_filter(array('id' => $userid, 'socialenable' => 1), 'social_filter_userid')),
        'social_disable' => new moodle_url('/local/community/plugins/social/index.php',
                array_filter(array('id' => $userid, 'socialenable' => 0), 'social_filter_userid'))
);

$userid = social_relevant_userid($userid);

// Save Moodle Log.
$eventdata['userid'] = $USER->id;
$eventdata['targetuserid'] = $userid;
\community_social\event\social_profile_view::create_event($USER->id, $eventdata)->trigger();

$PAGE->requires->js_call_amd('community_social/init', 'init', array($userid, $USER->id));

echo $OUTPUT->header();
echo html_writer::start_div('social');

if (social_if_wrong_user($userid)) {
    echo $OUTPUT->render_from_template('community_social/wrong-user', $data);
} else if (!social_has_permission($userid)) {
    echo $OUTPUT->render_from_template('community_social/no-permission', $data);
} else {

    // Get all social users.
    $sql = "
        SELECT csu.userid as id, csu.userid, u.firstname, u.lastname        
        FROM {community_social_usr_dtls} csu
        LEFT JOIN {user} u ON(csu.userid = u.id)
    ";
    $allsocialusersids = $DB->get_records_sql($sql);

    $data['profile_user_link'] = social_prepare_profile_user_link($userid);
    $data['editable'] = social_if_editable_profile($userid);
    $data['if_followers'] = social_if_user_followers($userid);
    $data['if_colleagues'] = social_if_user_colleagues($userid);
    $data = array_merge($data, social_get_user_info_block($userid));


    /*if ($userid == $USER->id) {
        // Lets get all public courses (hack)
        $data = array_merge($data, social_get_courses_pombim(0));
    } else {
        $data = array_merge($data, social_get_courses_pombim($userid));
    }*/

    $data = array_merge($data, social_get_courses_pombim($userid));
    $data = array_merge($data, social_get_courses_learn_stat($userid));

    // Prepare oer activities block.
    foreach ($data['oercatalog_activities'] as $item) {
        $data['oercatalog_activities']['blocks'][] = $item;

        // Update counter community_oer_wht_new.
        \community_oer\activity_oer::funcs()::whats_new_update_counter($item['cmid']);
    }

    $data['oercatalog_activities_enable'] = !empty($data['oercatalog_activities']) ? true : false;

    // Prepare oer courses block.
    $course = new \community_oer\course_oer;
    $elements = $course->query()->compareArrayField('users', 'userid', $userid)->compare('visible', '1');
    $elements = $course->calculate_data_online($elements, 'social');

    $oercourses = [];
    $cache = get_config('community_social', 'cache_viewed_oercourses');
    $cache = json_decode($cache, true);
    foreach ($elements->get() as $item) {

        // Get social collegues.
        $item->collegues = [];
        if (isset($cache[$item->cid])) {
            foreach ($cache[$item->cid] as $uid) {
                if (isset($allsocialusersids[$uid]) && $USER->id != $uid) {
                    $f = new \StdClass();
                    $f->firstname = $allsocialusersids[$uid]->firstname;
                    $f->lastname = $allsocialusersids[$uid]->lastname;

                    $item->collegues[] = $f;
                }
            }
        }

        $item->social_collegues_enable = !empty($item->collegues) ? true : false;

        $oercourses[$item->cid] = $item;
    }

    $oercourses = array_values($oercourses);

    if (!empty($oercourses)) {
        $data['oercatalog_courses']['blocks'] = $oercourses;
        $data['count_oercatalog_courses'] = count($data['oercatalog_courses']['blocks']);
        $data['oercatalog_courses_enable'] = true;
    }

    echo $OUTPUT->render_from_template('community_social/profile', $data);
}

echo html_writer::end_div();

echo $OUTPUT->footer();

set_user_preference('drawer-open-nav', $navdraweropen);
