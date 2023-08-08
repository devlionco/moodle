<?php
// Install H5P content type library
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Examples:
// php mod/hvp/editor/cli-install-content_type-library.php --installlib=H5P.CoursePresentation

$CFG->lang = 'en';
list($options, $unrecognized) = cli_get_params(
    array(
        'listlibs'    => false, // List all the H5P libs that can be updated.
        'installlib'  => false, // Name of H5P lib to be updated (and all its dependencies)
        'updateall'   => false, // Update all libs
        'contextid'   => 2, // Context of courseid=1 on any system;
        'help'        => false, // TODO...
    ),
    array(
        'l' => 'listlib',
        'i' => 'installlib',
        'u' => 'updateall',
        'h' => 'help'
    )
);

use mod_hvp\framework;

if ($options['listlibs']) {
    //$h5plibs = [];
    //$libs = $DB->get_records('qtype_hvp_libraries');
    $libs = $DB->get_records('hvp_libraries_hub_cache');
    if ($libs) {
        foreach($libs as $lib) {
            if (strpos($lib->machine_name, 'H5P.') === 0) {
                echo $lib->machine_name." \n";
                //$h5plibs[] = $lib->machine_name;
            }
        }
    }
    die;
}

if ($options['updateall']) {
    $editor = framework::instance('editor');
    //$libs = $DB->get_records('qtype_hvp_libraries');
    $libs = $DB->get_records('hvp_libraries_hub_cache');
    $_POST['contextId'] = $options['contextid'];
    $ValidToken = $editor->ajaxInterface->validateEditorToken($token);

    if ($libs) {
        foreach($libs as $lib) {
            if (strpos($lib->machine_name, 'H5P.') === 0) {
                echo " \n Updating: ".$lib->machine_name." \n";
                $editor->ajax->action(H5PEditorEndpoints::LIBRARY_INSTALL_CLI, $ValidToken, $lib->machine_name);
            }
        }
    }
    die;
}

// Install a single lib.
$editor = framework::instance('editor');

//$machinename = 'H5P.CoursePresentation';
$machinename = $options['installlib'];

// Used to check permission, and should be a course level context.
$_POST['contextId'] = $options['contextid'];

$ValidToken = $editor->ajaxInterface->validateEditorToken($token);
echo " \n Updating: ".$machinename." \n";
$editor->ajax->action(H5PEditorEndpoints::LIBRARY_INSTALL_CLI, $ValidToken, $machinename);
//$editor->ajax->libraryInstallCLI($machineName);