<?php

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../../../config.php');

require_login();
$stats_count = optional_param('stats_count', 0, PARAM_INT);
//$userid = optional_param('userid', 1, PARAM_INT);

//$DB->set_field('ally_warnings','stats', $stats_count, ['id'=>45]);
$DB->insert_record('ally_warnings', ['userid'=>$USER->id, 'stats'=>$stats_count,'timemodified'=>time()]);
echo " status updated = $stats_count";
// That's it :-)