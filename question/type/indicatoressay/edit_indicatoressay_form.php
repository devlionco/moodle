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
 * Defines the editing form for the indicatoressay question type.
 *
 * @package    qtype
 * @subpackage indicatoressay
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/question/type/indicatoressay/locallib.php');

/**
 * Indicatoressay question type editing form.
 *
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_indicatoressay_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $CFG, $OUTPUT, $USER, $DB, $PAGE, $COURSE;

        $qtype = question_bank::get_qtype('indicatoressay');

        // $parsedcategories = [];

        // if (isset($this->question->options)) {
        //     $categoriesweight = json_decode($this->question->options->categoriesweight);
        //     // foreach ($categoriesweight as $category) {
        //     //     $parsedcategories[] = ['name' => str_replace('_', ' ', $category->name), 'weight' => $category->weight];
        //     // }
        //     $parsedcategories = (array) $categoriesweight;
        // }
        // //add categoriers name,tag,weight,and selected status.
        $mform->addElement('header', 'questionindicatorheader', get_string('questionindicatorheader', 'qtype_indicatoressay'));
        $mform->setExpanded('questionindicatorheader');

        $settings = get_config('qtype_indicatoressay');
        list($isgradestypescalar, $indicators, $researchquestion) = qtype_indicatoressay_get_question_indicators($this->question->id ?? null);

        $mform->addElement('select', 'weightstyle',
            get_string('weightstyle', 'qtype_indicatoressay'), [
                0 => get_string('binary', 'qtype_indicatoressay'),
                1 => get_string('scalar', 'qtype_indicatoressay'),
            ]);
        $mform->setDefault('weightstyle', $isgradestypescalar);

        // Permissions.
        $attrs = !is_siteadmin() ? ['disabled' => 'disabled'] : [];

        $mform->addElement('select', 'researchquestion',
            get_string('researchquestion', 'qtype_indicatoressay'), [
                0 => get_string('no'),
                1 => get_string('yes'),
            ], $attrs);
        $mform->setDefault('researchquestion', $researchquestion);

        $mform->addElement('html', html_writer::div(get_string('questionindicatortext', 'qtype_indicatoressay'), 'm-4'));

        $availindicators = qtype_indicatoressay_get_available_indicators();
        $params['availindicatorsfull'] = array_values($availindicators);

        $availindicators = array_map(function ($ind) {
            return $ind->name;
        }, $availindicators);

        $types = [
            'type1' => get_string('type1', 'qtype_indicatoressay'),
            'type2' => get_string('type2', 'qtype_indicatoressay'),
            'type3' => get_string('type3', 'qtype_indicatoressay'),
        ];
        $params['types'] = $types;
        $params['availindicators'] = array_values($availindicators);

        $hascapedit = (object) [];

        $readonlytable = (!is_siteadmin() && $researchquestion) ? "1" : "0";
        // $readonlytable = $researchquestion;

        $htmldata = [
            $indicators,
            $params,
            $hascapedit,
            $readonlytable,
        ];

        $html = '
                <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
                <style>
                .tabulator-row:not(:hover).tabulator-selectable:not(:hover).tabulator-row-even .editablecol:not(:hover) {
                    background: #99c1de;
                }
                .tabulator-row:not(:hover).tabulator-selectable:not(:hover).tabulator-row-odd .editablecol:not(:hover) {
                    background: #bcd4e6;
                }
                </style>
                <button class="btn btn-secondary ml-0 mr-2" name="ind_addnew" id="ind_addnew" type="button">' . get_string('add') . '</button>
                <button class="btn btn-secondary ml-0 disabled mr-2" name="ind_delete" id="ind_delete" type="button"></button>
                <div id="questionindicatorfulltable-table"></div>
                <a href="#" "class" = "btn btn-success mr-2", "id" = "ind_submit"></a>
                ';
        $mform->addElement('html', $html, get_string('questionindicatortable', 'qtype_indicatoressay'), 'questionindicatortable');

        $mform->addElement('hidden', 'questionindicatorfulltable', '', ['id' => 'questionindicatorfulltable']);
        $mform->setType('questionindicatorfulltable', PARAM_RAW);

        $PAGE->requires->js_call_amd('qtype_indicatoressay/questionindicator', 'init', $htmldata);

        $mform->addElement('static', 'questionindicatorfake', ' ', ' ');

        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_indicatoressay'));
        $mform->setExpanded('responseoptions');

        $mform->addElement('select', 'responseformat',
            get_string('responseformat', 'qtype_indicatoressay'), $qtype->response_formats());
        $mform->setDefault('responseformat', $this->get_default_value('responseformat', 'editor'));

        $mform->addElement('select', 'responserequired',
            get_string('responserequired', 'qtype_indicatoressay'), $qtype->response_required_options());
        $mform->setDefault('responserequired', $this->get_default_value('responserequired', 1));
        $mform->hideIf('responserequired', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'responsefieldlines',
            get_string('responsefieldlines', 'qtype_indicatoressay'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', $this->get_default_value('responsefieldlines', 10));
        $mform->hideIf('responsefieldlines', 'responseformat', 'eq', 'noinline');

        // Create a text box that can be enabled/disabled for max/min word limits options.
        $wordlimitoptions = ['size' => '6', 'maxlength' => '6'];
        $mingrp[] = $mform->createElement('text', 'minwordlimit', '', $wordlimitoptions);
        $mform->setType('minwordlimit', PARAM_INT);
        $mingrp[] = $mform->createElement('checkbox', 'minwordenabled', '', get_string('enable'));
        $mform->setDefault('minwordenabled', 0);
        $mform->addGroup($mingrp, 'mingroup', get_string('minwordlimit', 'qtype_indicatoressay'), ' ', false);
        $mform->addHelpButton('mingroup', 'minwordlimit', 'qtype_indicatoressay');
        $mform->disabledIf('minwordlimit', 'minwordenabled', 'notchecked');
        $mform->hideIf('mingroup', 'responserequired', 'eq', '0');
        $mform->hideIf('mingroup', 'responseformat', 'eq', 'noinline');

        $maxgrp[] = $mform->createElement('text', 'maxwordlimit', '', $wordlimitoptions);
        $mform->setType('maxwordlimit', PARAM_INT);
        $maxgrp[] = $mform->createElement('checkbox', 'maxwordenabled', '', get_string('enable'));
        $mform->setDefault('maxwordenabled', 0);
        $mform->addGroup($maxgrp, 'maxgroup', get_string('maxwordlimit', 'qtype_indicatoressay'), ' ', false);
        $mform->addHelpButton('maxgroup', 'maxwordlimit', 'qtype_indicatoressay');
        $mform->disabledIf('maxwordlimit', 'maxwordenabled', 'notchecked');
        $mform->hideIf('maxgroup', 'responserequired', 'eq', '0');
        $mform->hideIf('maxgroup', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'attachments',
            get_string('allowattachments', 'qtype_indicatoressay'), $qtype->attachment_options());
        $mform->setDefault('attachments', $this->get_default_value('attachments', 0));

        $mform->addElement('select', 'attachmentsrequired',
            get_string('attachmentsrequired', 'qtype_indicatoressay'), $qtype->attachments_required_options());
        $mform->setDefault('attachmentsrequired', $this->get_default_value('attachmentsrequired', 0));
        $mform->addHelpButton('attachmentsrequired', 'attachmentsrequired', 'qtype_indicatoressay');
        $mform->hideIf('attachmentsrequired', 'attachments', 'eq', 0);

        $mform->addElement('filetypes', 'filetypeslist', get_string('acceptedfiletypes', 'qtype_indicatoressay'));
        $mform->addHelpButton('filetypeslist', 'acceptedfiletypes', 'qtype_indicatoressay');
        $mform->hideIf('filetypeslist', 'attachments', 'eq', 0);

        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'qtype_indicatoressay'), $qtype->max_file_size_options());
        $mform->setDefault('maxbytes', $this->get_default_value('maxbytes', 0));
        $mform->hideIf('maxbytes', 'attachments', 'eq', 0);

        // PTL_7328 Get Allowcheck option from config, w/o addind new field to 'qtype_indicatoressay_options'.
        $mform->addElement('select', 'allowcheck',
            get_string('allowcheck', 'qtype_indicatoressay'), [0 => get_string('no'), 1 => get_string('yes')]);
        $mform->setDefault('allowcheck', 0);

        $mform->addElement('header', 'responsetemplateheader', get_string('responsetemplateheader', 'qtype_indicatoressay'));
        $mform->addElement('editor', 'responsetemplate', get_string('responsetemplate', 'qtype_indicatoressay'),
            array('rows' => 10), $this->editoroptions); //array_merge($this->editoroptions, array('maxfiles' => 0))
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_indicatoressay');

        $mform->addElement('header', 'graderinfoheader', get_string('graderinfoheader', 'qtype_indicatoressay'));
        $mform->setExpanded('graderinfoheader');
        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_indicatoressay'),
            array('rows' => 10), $this->editoroptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (empty($question->options)) {
            return $question;
        }

        $question->responseformat = $question->options->responseformat;
        $question->responserequired = $question->options->responserequired;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->minwordenabled = $question->options->minwordlimit ? 1 : 0;
        $question->minwordlimit = $question->options->minwordlimit;
        $question->maxwordenabled = $question->options->maxwordlimit ? 1 : 0;
        $question->maxwordlimit = $question->options->maxwordlimit;
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;
        $question->filetypeslist = $question->options->filetypeslist;
        $question->allowcheck = get_config('qtype_indicatoressay', 'allowcheck_' . $question->id);
        $question->maxbytes = $question->options->maxbytes;

        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid, // Draftid
            $this->context->id, // context
            'qtype_indicatoressay', // component
            'graderinfo', // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo// text.
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        $question->responsetemplate = array(
            'text' => $question->options->responsetemplate,
            'format' => $question->options->responsetemplateformat,
        );

        return $question;
    }

    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        // Don't allow both 'no inline response' and 'no attachments' to be selected,
        // as these options would result in there being no input requested from the user.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachments']) {
            $errors['attachments'] = get_string('mustattach', 'qtype_indicatoressay');
        }

        // If 'no inline response' is set, force the teacher to require attachments;
        // otherwise there will be nothing to grade.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachmentsrequired']) {
            $errors['attachmentsrequired'] = get_string('mustrequire', 'qtype_indicatoressay');
        }

        // Don't allow the teacher to require more attachments than they allow; as this would
        // create a condition that it's impossible for the student to meet.
        if ($fromform['attachments'] > 0 && $fromform['attachments'] < $fromform['attachmentsrequired']) {
            $errors['attachmentsrequired'] = get_string('mustrequirefewer', 'qtype_indicatoressay');
        }

        if ($fromform['responserequired']) {
            if (isset($fromform['minwordenabled'])) {
                if (!is_numeric($fromform['minwordlimit'])) {
                    $errors['mingroup'] = get_string('err_numeric', 'form');
                }
                if ($fromform['minwordlimit'] < 0) {
                    $errors['mingroup'] = get_string('err_minwordlimitnegative', 'qtype_indicatoressay');
                }
                if (!$fromform['minwordlimit']) {
                    $errors['mingroup'] = get_string('err_minwordlimit', 'qtype_indicatoressay');
                }
            }
            if (isset($fromform['maxwordenabled'])) {
                if (!is_numeric($fromform['maxwordlimit'])) {
                    $errors['maxgroup'] = get_string('err_numeric', 'form');
                }
                if ($fromform['maxwordlimit'] < 0) {
                    $errors['maxgroup'] = get_string('err_maxwordlimitnegative', 'qtype_indicatoressay');
                }
                if (!$fromform['maxwordlimit']) {
                    $errors['maxgroup'] = get_string('err_maxwordlimit', 'qtype_indicatoressay');
                }
            }
            if (isset($fromform['maxwordenabled']) && isset($fromform['minwordenabled'])) {
                if ($fromform['maxwordlimit'] < $fromform['minwordlimit'] &&
                    $fromform['maxwordlimit'] > 0 && $fromform['minwordlimit'] > 0) {
                    $errors['maxgroup'] = get_string('err_maxminmismatch', 'qtype_indicatoressay');
                }
            }
        }
        return $errors;
    }

    public function qtype() {
        return 'indicatoressay';
    }
}
