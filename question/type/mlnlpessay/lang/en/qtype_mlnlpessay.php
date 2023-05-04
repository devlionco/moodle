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
 * Strings for component 'qtype_mlnlpessay', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype
 * @subpackage mlnlpessay
 * @copyright  Dor Herbesman  - Devlion
 */

$string['acceptedfiletypes'] = 'Accepted file types';
$string['rubiccategorytable'] = 'Rubic Category';
$string['acceptedfiletypes_help'] =
        'Accepted file types can be restricted by entering a list of file extensions. If the field is left empty, then all file types are allowed.';
$string['allowattachments'] = 'Allow attachments';
$string['attachedfiles'] = 'Attachments: {$a}';
$string['attachmentsoptional'] = 'Attachments are optional';
$string['attachmentsrequired'] = 'Require attachments';
$string['attachmentsrequired_help'] =
        'This option specifies the minimum number of attachments required for a response to be considered gradable.';
$string['formateditor'] = 'HTML editor';
$string['formateditorfilepicker'] = 'HTML editor with file picker';
$string['formatmonospaced'] = 'Plain text, monospaced font';
$string['formatnoinline'] = 'No online text';
$string['formatplain'] = 'Plain text';
$string['graderinfo'] = 'Information for graders';
$string['graderinfoheader'] = 'Grader Information';
$string['mustattach'] = 'When "No online text" is selected, or responses are optional, you must allow at least one attachment.';
$string['mustrequire'] = 'When "No online text" is selected, or responses are optional, you must require at least one attachment.';
$string['mustrequirefewer'] = 'You cannot require more attachments than you allow.';
$string['nlines'] = '{$a} lines';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';
$string['pluginname'] = 'MLNLPEssay';
$string['pluginname_help'] =
        'In response to a question, the respondent may upload one or more files and/or enter text online. A response template may be provided. Responses must be graded manually.';
$string['pluginname_link'] = 'question/type/mlnlpessay';
$string['pluginnameadding'] = 'Adding an MLNLP Essay question';
$string['pluginnameediting'] = 'Editing an MLNLP Essay question';
$string['pluginnamesummary'] =
        'Allows a response of a file upload and/or online text. This must then be graded manually and some strings i added .';
$string['privacy:metadata'] = 'The MLNLP Essay question type plugin does not store any personal data.';
$string['responsefieldlines'] = 'Input box size';
$string['responseformat'] = 'Response format';
$string['responseoptions'] = 'Response Options';
$string['responserequired'] = 'Require text';
$string['responsenotrequired'] = 'Text input is optional';
$string['responseisrequired'] = 'Require the student to enter text';
$string['responsetemplate'] = 'Response template';
$string['responsetemplateheader'] = 'Response Template';
$string['responsetemplate_help'] =
        'Any text entered here will be displayed in the response input box when a new attempt at the question starts.';
//mlnlp essay
$string['rubiccategoryheader'] = 'Rubic Category';
$string['rubiccategorychoose'] = 'Choose category';
$string['numberofcategories'] = 'How many categories?';
$string['numberofcategoriesdesc'] = 'Choose the number of categories inside mlnlp essay';
$string['categoryname'] = 'Category name';
$string['categorynamedesc'] = 'The name of the category';
$string['categorytag'] = 'Category tag';
$string['categorytagdesc'] = 'Some tag for the catgeory';
$string['modelname'] = 'Model name';
$string['modelnamedesc'] = 'Please insert model name';
$string['filename'] = 'File name';
$string['filenamedesc'] = 'Please insert the name of the file';
$string['weighterror'] = 'Error! weight value has to be numeric value!';
$string['true'] = 'True';
$string['false'] = 'False';
$string['pleaseselectananswer'] = 'Please write an answer.';
$string['weightforfeedback'] = 'Please wait for the system to analyze your answer to recive a grade.';
$string['numberofmodels'] = 'Number Of Models';
$string['numberofmodelsdesc'] = 'Number Of Models to use';
$string['categoryblock'] = '    ';
$string['categoryblockinfo'] = 'Category definition {$a}';
$string['svgfeedbacktemplatename'] = 'Feedback template (SVG)';
$string['svgfeedbacktemplatenamedesc'] = 'Placeholders {{cat1}} {{cat2}} ... {{cat13}} would be replaced by categories name';
$string['categorytype'] = "Please enter category type";
$string['categorytypes'] = "Please enter category types, one per row";
$string['svgfeedbacktemplate'] = 'SVG feedback template';
$string['type1'] = 'Type 1';
$string['type2'] = 'Type 2';
$string['graderesponse'] = 'Grade response';
$string['indextitle'] = '<h2><b>Index {$a} </b></h2>';
$string['descriptioncategory'] = 'Decscripton ';
$string['descriptioncategorydesc'] = 'View in responce table';
$string['nameresponce'] = 'Category Name';
$string['typeresponce'] = 'Type';
$string['resultresponce'] = 'Result';

$string['processing_mode'] = 'Processing mode';
$string['processing_mode_desc'] = 'Processing mode';
$string['processing_mode_random'] = 'Random (for testing)';
$string['processing_mode_local'] = 'Local';
$string['processing_mode_labmda'] = 'AWS Lambda';
$string['aws_labmda_key'] = 'AWS Lambda Key';
$string['aws_labmda_key_desc'] = 'AWS Lambda Key';
$string['aws_labmda_secret'] = 'AWS Lambda Secret';
$string['aws_labmda_secret_desc'] = 'AWS Lambda Secret';
$string['aws_labmda_region'] = 'AWS Lambda Region';
$string['aws_labmda_region_desc'] = 'AWS Lambda Region';
$string['aws_labmda_functionname'] = 'AWS Lambda Function Name';
$string['aws_labmda_functionname_desc'] = 'AWS Lambda Function Name';

$string['override'] = 'Override';
$string['cachedef_quizlambdawarmup'] = 'Quiz MLNLP question lambda warmup';
