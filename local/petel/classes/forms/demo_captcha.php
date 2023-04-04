<?php
/**
 * This file contains the form for demo script.
 *
 * @package local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author     2022 Devlion Ltd <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel\forms;

require_once($CFG->dirroot . '/lib/formslib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Class demo_captcha
 *
 * @copyright  2022 Devlion Ltd <info@devlion.co>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo_captcha extends \moodleform {

    /**
     * Define the form.
     */
    public function definition () {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'democaptchaheader', get_string('democaptchaheader', 'local_petel'));

        $mform->addElement('hidden', 'key', $this->_customdata['key']);
        $mform->setType('key', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $demosessiontimeout = isset($CFG->demosessiontimeout) ? $CFG->demosessiontimeout : 2;
        $mform->addElement('html', '
                <div class="alert">
                ' . get_string('democaptchadesc', 'local_petel', $demosessiontimeout) . '
                </div>
            ');

        $mform->addElement('recaptcha', 'democaptcha');
        $mform->addHelpButton('democaptcha', 'recaptcha', 'auth');
        $mform->closeHeaderBefore('democaptcha');

        $this->add_action_buttons(false, get_string('demosubmitlabel', 'local_petel'));
    }

    /**
     * Perform some moodle validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $recaptchaelement = $this->_form->getElement('democaptcha');

        if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
            $response = $this->_form->_submitValues['g-recaptcha-response'];
            if (!$recaptchaelement->verify($response)) {
                $errors['democaptcha'] = get_string('incorrectpleasetryagain', 'auth');
            }
        } else {
            $errors['democaptcha'] = get_string('missingrecaptchachallengefield');
        }

        return $errors;
    }
}


