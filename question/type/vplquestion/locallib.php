<?php
// This file is part of Moodle - https://moodle.org/
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
 * Lib for vplquestion question type.
 * @package    qtype_vplquestion
 * @copyright  Astor Bizard, 2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('QVPL', 'qtype_vplquestion');

/**
 * Format and filter execution files provided by the user.
 * This method adds a suffix (_qvpl) to file names, and filters out files specified as UNUSED.
 * @param array $execfiles The files to format and filter.
 * @param array $selector If specified, only the files with name contained in this array will be considered.
 * @return array The resulting files array.
 */
function qtype_vplquestion_format_execution_files($execfiles, $selector=null) {
    $formattedfiles = array();
    foreach ($execfiles as $name => $content) {
        if ($selector === null || in_array($name, $selector)) {
            if (substr($content, 0, 6) != 'UNUSED') {
                $formattedfiles[$name.'_qvpl'] = $content;
            }
        }
    }
    return $formattedfiles;
}

/**
 * Insert answer into required file and format it for submission.
 * @param object $question The question data.
 * @param string $answer The answer to the question, to include in submission.
 * @return array Files ready for submission.
 */
function qtype_vplquestion_get_reqfile_for_submission($question, $answer) {
    global $CFG;
    require_once($CFG->dirroot .'/mod/vpl/vpl.class.php');
    $vpl = new mod_vpl($question->templatevpl);

    $reqfiles = $vpl->get_required_fgm()->getAllFiles();
    $reqfilename = array_keys($reqfiles)[0];

    // Escape all backslashes, as following operation deletes them.
    $answer = preg_replace('/\\\\/', '$0$0', $answer);
    // Replace the {{ANSWER}} tag, propagating indentation.
    $answeredreqfile = preg_replace('/([ \t]*)(.*)\{\{ANSWER\}\}/i',
            '$1${2}'.implode("\n".'${1}', explode("\n", $answer)),
            $question->templatecontext);

    return array($reqfilename => $answeredreqfile);
}

/**
 * Evaluate an answer to a question by submitting it to the VPL and requesting an evaluate.
 * @param string $answer The answer to evaluate.
 * @param object $question The question data.
 * @param bool $deletesubmissions Whether user submissions should be discarded at the end of the operation.
 * @return object The evaluation result.
 */
function qtype_vplquestion_evaluate($answer, $question, $deletesubmissions) {
    global $USER, $CFG;
    require_once($CFG->dirroot .'/mod/vpl/vpl.class.php');
    require_once($CFG->dirroot .'/mod/vpl/forms/edit.class.php');
    $userid = $USER->id;
    $vpl = new mod_vpl($question->templatevpl);

    $reqfile = qtype_vplquestion_get_reqfile_for_submission($question, $answer);
    $execfiles = qtype_vplquestion_format_execution_files(json_decode($question->execfiles));
    $files = $reqfile + $execfiles;

    $lockfactory = \core\lock\lock_config::get_lock_factory('qtype_vplquestion_evaluate');

    // Try to evaluate several times (as internal evaluation errors may occur).
    $tries = 0;
    do {
        $lock = null;
        $tries++;
        try {
            $lastmessage = '';
            $serverwassilent = true;

            // Forbid simultaneous evaluations (as the VPL won't allow multiple executions at once).
            $lock = $lockfactory->get_lock($userid, 5);
            if ($lock === false) {
                throw new moodle_exception('locktimeout');
            }

            mod_vpl_edit::save($vpl, $userid, $files);

            $coninfo = mod_vpl_edit::execute($vpl, $userid, "evaluate");

            $wsprotocol = $coninfo->wsProtocol;
            if ( $wsprotocol == 'always_use_wss' ||
                    ($wsprotocol == 'depends_on_https' && stripos($_SERVER['SERVER_PROTOCOL'], 'https') !== false) ) {
                $port = $coninfo->securePort;
                $protocol = 'https://';
            } else {
                $port = $coninfo->port;
                $protocol = 'http://';
            }

            // Set up a curl execution to listen to VPL execution server,
            // so we can stop as soon as we get the 'retrieve:' message (meaning that evaluation is complete).
            $ch = curl_init($protocol . $coninfo->server . ':' . $port . '/' . $coninfo->monitorPath);

            // Make a key that is 16 characters long before encoding, to match the RFC6455 standard.
            $wskey = base64_encode(substr(uniqid('qvpl'), 1));

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Connection: Upgrade",
                    "Upgrade: websocket",
                    "Host: $coninfo->server",
                    "Sec-WebSocket-Key: $wskey",
                    "Sec-WebSocket-Version: 13"
            ));

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($chdummy, $data) use (&$lastmessage, &$serverwassilent) {
                $lastmessage = fix_utf8($data);
                $serverwassilent = false;
                if (strpos($data, 'retrieve:') !== false) {
                    // Interrupt curl exec.
                    return -1;
                }
                return strlen($data);
            });

            curl_exec($ch);
            curl_close($ch);

            $result = new stdClass();
            $result->vplresult = mod_vpl_edit::retrieve_result($vpl, $userid);
            $result->lastmessage = $lastmessage;
            $result->serverwassilent = $serverwassilent;
            $retry = false;

        } catch (Exception $e) {
            // There was an error during evaluation - retry.
            $result = new stdClass();
            $result->vplresult = new stdClass();
            $result->lastmessage = $lastmessage . "\n" . $e->getMessage();
            $result->serverwassilent = $serverwassilent;
            $retry = true;
        } finally {
            // Always release locks.
            if ($lock) {
                $lock->release();
            }
        }

        // Retry up to 10 times.
    } while ($retry && $tries < 10);

    if ($deletesubmissions) {
        try {
            require_once($CFG->dirroot.'/mod/vpl/vpl_submission.class.php');
            $subids = $vpl->user_submissions($userid);
            if ($subids) {
                foreach ($subids as $subid) {
                    $submission = new mod_vpl_submission($vpl, $subid);
                    $submission->delete();
                }
            }
        } catch (Exception $e) {
            // Something went wrong while deleting submissions - do nothing more.
            return $result;
        }
    }

    return $result;
}

/**
 * Compute the fraction (grade between 0 and 1) from the result of an evaluation.
 * @param object $result The evaluation result.
 * @param int $templatevpl The ID of the VPL this evaluation has been executed on.
 * @return float|null The fraction if any, or null if there was no grade.
 */
function qtype_vplquestion_extract_fraction($result, $templatevpl) {
    if ($result->grade) {
        global $CFG;
        require_once($CFG->dirroot .'/mod/vpl/vpl.class.php');
        $maxgrade = (new mod_vpl($templatevpl))->get_grade();
        $fraction = floatval(preg_replace('/.*: (.*) \/.*/', '$1', $result->grade)) / $maxgrade;
        return $fraction;
    } else {
        return null;
    }
}
