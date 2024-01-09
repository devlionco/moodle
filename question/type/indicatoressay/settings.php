<?php
/**
 * Defines the editing form for the essay question type.
 *
 * @package    qtype
 * @subpackage indicatoressay
 * @copyright  2023 Anton P. Devlion
 */

defined('MOODLE_INTERNAL') || die();

$settings = null;

if ($hassiteconfig) {
    /** @var admin_root $ADMIN */
    $ADMIN->add('qtypesettings', new admin_category('qtype_indicatoressay_category', get_string('pluginname', 'qtype_indicatoressay')));
    $settingspage = new admin_settingpage('indicatoressaysettings', get_string('indicatoressaysettings', 'qtype_indicatoressay'));
    // if ($ADMIN->fulltree) {

    //     // Number of activities to customize settings
    //     $name = 'qtype_indicatoressay/numberofcategories';
    //     $title = get_string('numberofcategories', 'qtype_indicatoressay');
    //     $description = get_string('numberofcategoriesdesc', 'qtype_indicatoressay');
    //     $default = 1;

    //     $choices = [];
    //     for ($i = 1; $i < 21; $i++) {
    //         $choices[$i] = $i;
    //     }
    //     $settingspage->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    //     $choices = [];
    //     for ($i = 1; $i < 6; $i++) {
    //         $choices[$i] = $i;
    //     }

    //     $name = 'qtype_indicatoressay/numberofmodels';
    //     $title = get_string('numberofmodels', 'qtype_indicatoressay');
    //     $description = get_string('numberofmodelsdesc', 'qtype_indicatoressay');
    //     $default = 1;
    //     $settingspage->add(new admin_setting_configselect($name, $title, $description, 1, $choices));

    //     $name = 'qtype_indicatoressay/categorytypes';
    //     $title = get_string('categorytypes', 'qtype_indicatoressay');
    //     $description = get_string('categorytypes', 'qtype_indicatoressay');
    //     $settingspage->add(new admin_setting_configtextarea($name, $title, $description, '', PARAM_TEXT, 60, 4));

    //     $numberofcategories = get_config('qtype_indicatoressay', 'numberofcategories');
    //     for ($i = 1; $i <= $numberofcategories; ++$i) {
    //         $settingspage->add(
    //             new admin_setting_heading(
    //                 'category' . $i,
    //                 get_string('categoryblock', 'qtype_indicatoressay', $i),
    //                 get_string('categoryblockinfo', 'qtype_indicatoressay', $i)
    //             )
    //         );
    //         $indexname = "indextitle" . ($i - 1);
    //         $indextitle = get_string('indextitle', 'qtype_indicatoressay', $i);
    //         $indexdescription = '';
    //         $setting = new admin_setting_description($indexname, $indextitle, $indexdescription);
    //         $settingspage->add($setting);
    //         // Set category name and tag
    //         $settingspage->add(
    //             new admin_setting_configtext(
    //                 'qtype_indicatoressay/category' . $i . 'name',
    //                 get_string('categoryname', 'qtype_indicatoressay', $i),
    //                 get_string('categorynamedesc', 'qtype_indicatoressay'),
    //                 '',
    //                 PARAM_TEXT
    //             )
    //         );

    //         $settingspage->add(
    //             new admin_setting_configtext(
    //                 'qtype_indicatoressay/tag' . $i . 'name',
    //                 get_string('categorytag', 'qtype_indicatoressay', $i),
    //                 get_string('categorytagdesc', 'qtype_indicatoressay'),
    //                 '',
    //                 PARAM_TEXT
    //             )
    //         );

    //         $settingspage->add(
    //             new admin_setting_configtextarea(
    //                 'qtype_indicatoressay/category' . $i . "description",
    //                 get_string('descriptioncategory', 'qtype_indicatoressay', $i),
    //                 get_string('descriptioncategorydesc', 'qtype_indicatoressay'),
    //                 '',
    //                 PARAM_TEXT
    //             )
    //         );
    //     }

    // }
    $ADMIN->add('qtype_indicatoressay_category', $settingspage);
    $ADMIN->add('qtype_indicatoressay_category',
        new admin_externalpage(
            'qtype_indicatoressay_indicators',
            get_string('indicatorssettings', 'qtype_indicatoressay'),
            new moodle_url('/question/type/indicatoressay/indicators.php')));
}
