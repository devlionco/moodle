<?php

require_once(__DIR__.'/../../../../../config.php');

// https://docs.moodle .......

//$PAGE =0;
$PAGE->set_url('/local/lib/editor/atto/plugins/a11yaxe/test_axe.php');
$PAGE->set_title('Test axe');
$PAGE->set_heading('Test axe');
$context = context_course::instance(1);
$PAGE->set_context($context);

//$OUTPUT =0;

echo $OUTPUT->header();
echo "Hello Oren";

// https ....
echo html_writer::start_div('<p><blink>Moving Sale Thursday!</blink></p>');
echo html_writer::start_div('<li>Coffee</li>
<li>Tea</li>
<li>Milk</li>');

$PAGE->requires->js_amd_inline("require(['jquery', 'atto_a11yaxe/axe'], function($, Axe) {
            console.log('Loading Axe...');
            axe.run(
                {
                    include: [['#region-main']]
                },
                {
                    rules: {
                        'link-in-text-block': { enabled: true },
                        'p-as-heading': { enabled: true }
                    }
                },
                function(err, results) {
                    if (err) throw err;
                    console.log(results.violations);
                }
            );
            console.log( 'Axe ready!' );

});");

echo $OUTPUT->footer();

