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
 * Local plugin "OER catalog" - Library
 *
 * @package     community_social
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function community_social_render_navbar_output() {
    global $CFG, $USER, $PAGE;

    $output = '';

    if (!social_has_permission($USER->id)) {
        return $output;
    }
    $active = ($PAGE->pagetype === 'local-community-plugins-social-teachers'
            || $PAGE->pagetype === 'local-community-plugins-social-profile') ? 'active' : '';

    $name = get_string('thesocialarea', 'community_social');
    $output = '
                <li class="nav-item d-flex align-items-center">
                <div class="social-nav float-right popover-region">
                <a class="nav-headeritem ' . $active . ' nav-link" href="' . $CFG->wwwroot .
            '/local/community/plugins/social/index.php" tabindex="0" role="button">
                    <i class="fal fa-users d-flex d-lg-none" aria-hidden="true" title="' . $name . '"></i>
                    <p class="d-none d-lg-flex mb-0">' . $name . '</p>
                </a>
               </div>
               </li>';
    return $output;
}
