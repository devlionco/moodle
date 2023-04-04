<?php

/**
 * OPCache - Invalidate PHP file, flush it from cache or compile it.
 *
 * @package    local_petel
 * @copyright  Nadav Kavalerchik <nadav.kaalerchik@weizmann.ac.il>
 * @auther     Nadav Kavalerchik
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

include(__DIR__ . '/../../../config.php');
require_once("{$CFG->libdir}/clilib.php");

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'file' => false,
        'ump' => false,
    ),
    array(
        'h' => 'help',
        'f' => 'file',
        'u' => 'ump',
    )
);

if ($options['help'] || empty($options['file'])) {
    $help = <<<EOT
Invalidate opcache file

Options:
 -h, --help      Print out this help
 -f, --file      Filename, including absolute path.
 -u, --ump       Use Moodle Path, and allow file to relative.

Example:
\$sudo -u www-data /usr/bin/php opcache_invalidate_file -f=/var/www/moodle/config.php

EOT;

    echo $help;
    die;
}

if ($options['ump']) {
    $full_filename = $CFG->dirroot .'/'. $options['file'];
} else {
    $full_filename = $options['file'];
}
//echo $full_filename.PHP_EOL;
flush_file($full_filename);

function flush_file($filename) {

    if (function_exists('opcache_invalidate')) {
        return opcache_invalidate($filename, true);
    } else {
        if (function_exists('apc_compile_file')) {
            return apc_compile_file($filename);
        }
    }
    return false;
}