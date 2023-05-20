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
 * Question type class for the hvp question type.
 *
 * @package    qtype
 * @subpackage hvp
 * @copyright 2022 onwards SysBind  {@link http://sysbind.co.il}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_hvp_library\H5PCore as H5PCore;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/hvp/question.php');
require_once($CFG->dirroot . '/question/type/hvp/classes/framework.php');

/**
 * The hvp question type class.
 *
 */
class qtype_hvp extends question_type {
    public function save_question($question, $form) {
        $result = parent::save_question($question, $form);
        if ($form->h5paction === 'upload') {
            $form->uploaded = true;
            $h5pstorage = \qtype_hvp\framework::instance('storage');
            $h5pstorage->savePackage((array)$form);
            $hvpid = $h5pstorage->contentId;
        } else {
            $core = \qtype_hvp\framework::instance();
            $editor = \qtype_hvp\framework::instance('editor');

            if (!empty($question->id)) {
                $content = $core->loadContent($question->id);
                if (!empty($content)) {
                    $form->id = $content['id'];
                    $oldlib = $content['library'];
                    $oldparams = json_decode($content['params']);
                } else {
                    $form->id = null;
                }
            }
            $form->library = H5PCore::libraryFromString($form->h5plibrary);

            $form->library['libraryId'] = $core->h5pF->getLibraryId($form->library['machineName'],
                $form->library['majorVersion'],
                $form->library['minorVersion']);

            $form->question = $result->id;
            $core->saveContent((array)$form);

            $params = json_decode($form->params);

            $editor->processParameters($form, $form->library, $params,
                isset($oldlib) ? $oldlib : null,
                isset($oldparams) ? $oldparams : null);
        }

        return $result;
    }

    protected function patch_filenames($hvpid) {
        global $DB;

        $jsoncontent = $DB->get_field('qtype_hvp', 'json_content', ['id' => $hvpid]);

        $content = json_decode($jsoncontent);

        $this->patch_content_filenames($content);

        $jsoncontent = json_encode($content);

        $DB->set_field('qtype_hvp', 'json_content', $jsoncontent, ['id' => $hvpid]);
    }

    protected function patch_content_filenames(&$content) {
        foreach ($content as $property => &$value) {
            if (($property == 'path') && is_string($value) && (substr($value, -4) == '#tmp')) {
                $value = substr($value, 0, -4);
            }

            if (is_object($value) || is_array($value)) {
                $this->patch_content_filenames($value);
            }
        }
    }
}
