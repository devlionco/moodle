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

namespace format_flexsections\output\courseformat;

use course_modinfo;
use html_writer;
use stdClass;

/**
 * Render a course content.
 *
 * @package   format_flexsections
 * @copyright 2022 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends \core_courseformat\output\local\content {

    /** @var \format_flexsections the course format class */
    protected $format;

    /** @var bool Flexsections format has add section. */
    protected $hasaddsection = true;

    /**
     * Template name for this exporter
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_flexsections/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE, $OUTPUT, $COURSE, $DB;
        $data = parent::export_for_template($output);

        // If we are on course view page for particular section.
        if ($this->format->get_viewed_section()) {
            // Do not display the "General" section when on a page of another section.
            if ($this->format->get_format_option('section0') == FORMAT_FLEXSECTIONS_SECTION0_COURSEPAGE) {
                $data->initialsection = null;
            }

            // Add 'back to parent' control.
            $section = $this->format->get_section($this->format->get_viewed_section());
            if ($section->parent) {
                $sr                  = $this->format->find_collapsed_parent($section->parent);
                $url                 = $this->format->get_view_url($section->section, array('sr' => $sr));
                $data->backtosection = [
                    'url'         => $url->out(false),
                    'sectionname' => $this->format->get_section_name($section->parent),
                ];
            } else {
                $sr                 = 0;
                $url                = $this->format->get_view_url($section->section, array('sr' => $sr));
                $context            = \context_course::instance($this->format->get_courseid());
                $data->backtocourse = [
                    'url'        => $url->out(false),
                    'coursename' => format_string($this->format->get_course()->fullname, true, ['context' => $context]),
                ];
            }

            // Hide add section link below page content.
            $data->numsections = false;
        }

        // On the course main page, display this section as a card unless the
        // user is currently editing the page. Section #0 should never be
        // displayed as a card.
        //$issinglesectionpage = $this->format->get_section_number() != 0;
        $data->showascard = !$PAGE->user_is_editing();

        $courseimage = \core_course\external\course_summary_exporter::get_course_image($this->format->get_course());
        if (!$courseimage) {
            $courseimage = $OUTPUT->get_generated_image_for_id($this->format->get_course()->id);
        }
        $data->courseimageurl = $courseimage;

        // Uploadcourseimage.
        $showuploadcourseimage = false;
        if ($PAGE->user_is_editing() && $COURSE->id > 1 && (substr($PAGE->pagetype, 0, strlen('course-view')) === 'course-view')) {
            $showuploadcourseimage = true;
            $PAGE->requires->js_call_amd('format_flexsections/courseimage', 'init');
            $data->sesskey  = sesskey();
            $data->courseid = $COURSE->id;
        }
        $data->showuploadcourseimage = $showuploadcourseimage;

        // Enrolkey.
        $enrolkeybtn = false;
        if ($PAGE->user_allowed_editing() && $PAGE->pagelayout === 'course') {
            $instances = $DB->get_records('enrol', array('courseid' => $PAGE->course->id, 'enrol' => 'self'));
            foreach ($instances as $instance) {
                if (!empty($instance->password)) {
                    $title          = get_string('studentsenrolkey', 'theme_petel', $instance->password);
                    $icon           = $OUTPUT->pix_icon('i/info', '', 'moodle', array('class' => 'm-0'));
                    $enrolkeybtn    = html_writer::tag('button', $icon . $title, array('id' => 'enrolkeybtn', 'class' => 'btn btn-sm btn-secondary', 'aria-label' => $title));
                    $context_header = null;
                    $PAGE->requires->js_call_amd('format_flexsections/enrolkey', 'init_dialog', array($context_header, $instance->password));
                    $PAGE->requires->strings_for_js(array('getcoursekeytitle', 'getkey', 'cancel'), 'theme_petel');
                }
            }
        }
        $data->enrolkeybtn = $enrolkeybtn;

        // Course completion.
        $defaults = get_config('format_flexsections');
        if ($defaults->showprogress == FORMAT_FLEXSECTIONS_SHOWPROGRESS_SHOW) {
            $coursecompletion       = $this->get_course_completion($this->format->get_course()->id);
            $data->coursecompletion = $coursecompletion;
            $progressformat         = $defaults->progressformat;
            $progressmode           = $defaults->progressmode;
            $iscomplete             = $coursecompletion['total'] == $coursecompletion['completed'];
            $data->showpercentage   = !$iscomplete && $progressformat == FORMAT_FLEXSECTIONS_PROGRESSFORMAT_PERCENTAGE;
            $data->modecircle       = !$iscomplete && $progressmode == FORMAT_FLEXSECTIONS_PROGRESSMODE_CIRCLE;
            $data->showcount        = !$iscomplete && $progressformat == FORMAT_FLEXSECTIONS_PROGRESSFORMAT_COUNT;
        }

        // courselinks
        $iscoursepage = preg_match("/course-view/", $PAGE->pagetype);
        $data->courselinks = $iscoursepage ? \theme_petel\output\core_renderer::course_links() : '';

        return $data;
    }

    function get_course_completion($courseid) {
        global $DB, $USER;
    
        // Get the course modules in the course.
        $courseModules = $DB->get_records('course_modules', ['course' => $courseid]);
    
        if (empty($courseModules)) {
            // No modules found in the course.
            return [
                'total' => 0,
                'completed' => 0,
                'percentage' => 0,
                'iscomplete' => false
            ];
        }
    
        $courseModuleIds = array_keys($courseModules);
        $completedModules = $DB->count_records_select('course_modules_completion',
            'coursemoduleid IN (' . implode(',', $courseModuleIds) . ') AND userid = :userid',
            ['userid' => $USER->id]
        );
    
        $totalModules = count($courseModules);
        $percentage = round(($completedModules / $totalModules) * 100);
        $isComplete = ($completedModules == $totalModules);
    
        return [
            'total' => $totalModules,
            'completed' => $completedModules,
            'percentage' => $percentage,
            'iscomplete' => $isComplete
        ];
    }
    

    /**
     * Export sections array data.
     *
     * TODO: this is an exact copy of the parent function because get_sections_to_display() is private
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    protected function export_sections(\renderer_base $output): array{

        $format  = $this->format;
        $course  = $format->get_course();
        $modinfo = $this->format->get_modinfo();

        // Generate section list.
        $sections        = [];
        $stealthsections = [];
        $numsections     = $format->get_last_section_number();
        foreach ($this->get_sections_to_display($modinfo) as $sectionnum => $thissection) {
            // The course/view.php check the section existence but the output can be called
            // from other parts so we need to check it.
            if (!$thissection) {
                throw new \moodle_exception('unknowncoursesection', 'error',
                    course_get_url($course), format_string($course->fullname));
            }

            $section = new $this->sectionclass($format, $thissection);

            if ($sectionnum > $numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                if (!empty($modinfo->sections[$sectionnum])) {
                    $stealthsections[] = $section->export_for_template($output);
                }
                continue;
            }

            if (!$format->is_section_visible($thissection)) {
                continue;
            }

            $sections[] = $section->export_for_template($output);
        }
        if (!empty($stealthsections)) {
            $sections = array_merge($sections, $stealthsections);
        }
        return $sections;
    }

    /**
     * Return an array of sections to display.
     *
     * This method is used to differentiate between display a specific section
     * or a list of them.
     *
     * @param course_modinfo $modinfo the current course modinfo object
     * @return \section_info[] an array of section_info to display
     */
    private function get_sections_to_display(course_modinfo $modinfo): array{
        $viewedsection = $this->format->get_viewed_section();
        return array_values(array_filter($modinfo->get_section_info_all(), function ($s) use ($viewedsection) {
            return (!$s->section) ||
                (!$viewedsection && !$s->parent && $this->format->is_section_visible($s)) ||
                ($viewedsection && $s->section == $viewedsection);
        }));
    }
}
