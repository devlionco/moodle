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
 * Serve question type files
 *
 * @since      Moodle 2.0
 * @package    qtype_numerical
 * @copyright  Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Checks file access for numerical questions.
 *
 * @package  qtype_numerical
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return array
 */
function qtype_numerical_get_units_array() {

    $arr = qtype_numerical_prepare_units();

    $result = [];
    if(!empty($arr)){
        foreach($arr as $group){
            foreach($group as $item){
                if(!empty($item['unit'])) {
                    $result[$item['unit']] = $item['unit'];
                }
            }
        }
    }

    return $result;
}

function qtype_numerical_get_units_row_array($danounit) {
    global $CFG, $DB;

    $arr = qtype_numerical_prepare_units();

    if(!empty($arr)){
        //Find needed row
        foreach($arr as $key=>$group){
            foreach($group as $item){
                if($item['unit'] == $danounit){
                    $row_id_dano = $key;
                }
            }
        }

        if(!isset($row_id_dano)) return false;

    }else{
        return array();
    }


    if(!empty($arr)){

        $result = array();
        foreach($arr as $key=>$group){
            foreach($group as $item){
                if($key == $row_id_dano){
                    $result[$item['unit']] = $item['unit'];
                }
            }
        }

        return $result;

    }else{
        return array();
    }

}

////Test
//    $dano['value'] = 30;
//    $dano['unit'] = 'kg';
//
//    $answer['value'] = 30*1000;
//    $answer['unit'] = 'gr';
//    $tolerance = 0.01;
//
//var_dump(qtype_numerical_check_for_penalty($dano, $answer, $tolerance));exit;

//    $dano['value'] = 30;
//    $dano['unit'] = 'm';
//
//    $answer['value'] = 10;
//    $answer['unit'] = 'mm';
//    $tolerance = 0.01;

function qtype_numerical_check_for_penalty($dano, $answer, $tolerance) {

    // Conversion K => C.
    if(in_array($dano['unit'], ['C', '°C']) && $answer['unit'] == 'K'){
        $answer['unit'] = $dano['unit'];
        $answer['value'] = $answer['value'] - 273;
    }

    // Conversion C => K.
    if($dano['unit'] == 'K' && in_array($answer['unit'], ['C', '°C'])){
        $answer['unit'] = 'K';
        $answer['value'] = $answer['value'] + 273;
    }

    $unit_validation_aprox = 0;
    $unit_validation_accur = 0;
    $value_validation = 0;

    $dano['unit'] = trim($dano['unit']);
    $answer['unit'] = trim($answer['unit']);

    $obj = new \stdClass;
    $obj->result = false;
    $obj->penalty = 0;

    if(empty($answer['unit'])){
        $answer['unit'] = 'empty unit';
    }

    //Compare Unit
    $arr_units = qtype_numerical_get_units_row_array($dano['unit']);
    if(empty($arr_units)){return $obj;}

    if(in_array($answer['unit'], $arr_units)){
        $unit_validation_aprox = 1;
    }

    if($answer['unit']==$dano['unit']){
        $unit_validation_accur = 1;
    }

    //Compare Value
    $arr = qtype_numerical_prepare_units();
    if(empty($arr)){return $obj;}

    //Find needed row
    foreach($arr as $key=>$group){
        foreach($group as $item){
            if($item['unit'] == $dano['unit']){
                $row_id_dano = $key;
            }
        }
    }

    //Get coefficients
    $arr_coeff_units = array();
    foreach($arr[$row_id_dano] as $item){
        if($item['unit'] == $dano['unit']){
            $coeff_dano = $item['value'];
        }

        $arr_coeff_units[] = $item['value'];
    }

    //Prepare new values with selected coeff
    $arr_coeff_units_new = array();
    foreach($arr_coeff_units as $item){
        if (floatval($coeff_dano) != 0) {
            $arr_coeff_units_new[] = floatval($item) * (floatval($dano['value'])/floatval($coeff_dano));
        }else{
            $arr_coeff_units_new[] = floatval($item);
        }
    }


    //Compare with tolerance
    foreach($arr_coeff_units_new as $item){
        $coefftolerance = ($dano['value'] != 0) ? ($item/$dano['value']) * $tolerance : 0;
        if(qtype_numerical_compare_with_tolerance($item, $answer['value'], $coefftolerance)){
            $value_validation = 1;

        }
    }

    if(!$value_validation && !$unit_validation_aprox && !$unit_validation_accur){
        return $obj;
    }

    $wrongvaluepenalty = qtype_numerical_get_wrongvaluepenalty();
    $wrongunitpenalty = qtype_numerical_get_wrongunitpenalty();

    if($wrongvaluepenalty != '0' && $wrongunitpenalty != '0'){
        if($value_validation){
            $obj->result = true;
            $obj->penaltytype = 'unit';
            $obj->penalty = $wrongunitpenalty;
            $obj->feedback = get_string('feedbackwrongunit', 'qtype_numerical');
        }elseif($unit_validation_aprox){
            $obj->result = true;
            $obj->penaltytype = 'value';
            $obj->penalty = $wrongvaluepenalty;
            $obj->feedback = get_string('feedbackwrongvalue', 'qtype_numerical');
        }
    }

    return $obj;
}

function qtype_numerical_compare_answer($dano, $answer, $tolerance) {
    global $CFG, $DB;

    if(empty($answer['unit'])){
        return false;
    }

    // Conversion K => C.
    if(in_array($dano['unit'], ['C', '°C']) && $answer['unit'] == 'K'){
        $answer['unit'] = $dano['unit'];
        $answer['value'] = $answer['value'] - 273;
    }

    // Conversion C => K.
    if($dano['unit'] == 'K' && in_array($answer['unit'], ['C', '°C'])){
        $answer['unit'] = 'K';
        $answer['value'] = $answer['value'] + 273;
    }

    $dano['unit'] = trim($dano['unit']);
    $answer['unit'] = trim($answer['unit']);

    $arr = qtype_numerical_prepare_units();
    if(!empty($arr)){
        //Find needed row
        foreach($arr as $key=>$group){
            foreach($group as $item){
                if($item['unit'] == $dano['unit']){
                    $row_id_dano = $key;
                }

                if($item['unit'] == $answer['unit']){
                    $row_id_answer = $key;
                }
            }
        }

        if(!isset($row_id_dano) || !isset($row_id_answer)) return false;

        //Diffrent rows
        if($row_id_dano != $row_id_answer) return false;

    }else{
        return false;
    }

    //If unit dano == unit answer
    if($dano['unit'] == $answer['unit']){
        return qtype_numerical_compare_with_tolerance($dano['value'], $answer['value'], $tolerance);
    }

    //Get coefficients
    foreach($arr[$row_id_dano] as $item){
        if($item['unit'] == $dano['unit']){
            $coeff_dano = $item['value'];
        }
        if($item['unit'] == $answer['unit']){
            $coeff_answer = $item['value'];
        }
    }

    if (floatval($coeff_dano) != 0) {
        $new_dano = $coeff_answer*($dano['value']/floatval($coeff_dano));
    }else{
        $new_dano = $coeff_answer;
    }

    $coefftolerance = ($dano['value'] != 0) ? ($new_dano/$dano['value']) * $tolerance : 0;

    return qtype_numerical_compare_with_tolerance($new_dano, $answer['value'], $coefftolerance);

}

function qtype_numerical_compare_with_tolerance($dano, $answer, $tolerance) {
    if($tolerance != 0){
        //$left = $dano - abs($dano * $tolerance);
        //$right = $dano + abs($dano * $tolerance);

        $left = $dano - $tolerance;
        $right = $dano + $tolerance;
        if($left < $answer && $answer < $right){
            return true;
        }else{
            return false;
        }
    }else{
        if($dano == $answer) return true;
        else return false;
    }
}

function qtype_numerical_prepare_units() {
    global $CFG, $DB;

    $sql = "SELECT * FROM {config} WHERE name='qtype_numerical_units'";
    $config = $DB->get_record_sql($sql);

    if(!empty($config)){
        $setting = $config->value;
        $arr_lines = preg_split('/\r\n|[\r\n]/', $setting);

        $arr_groups_tmp = array();
        foreach($arr_lines as $line){

            // Remove spaces.
            $line = str_replace(' ', '', $line);

            $arr_groups_tmp[] = explode('=', $line);
        }

        //Create array data
        $arr_groups = array();
        foreach($arr_groups_tmp as $group){

            $arr_item = array();
            foreach($group as $item){
                $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$item);

                if(count($arr) != 2 && strpos($item, '°') !== false){
                    $arr = array();
                    $arr[0] = (double) $item;
                    $arr[1] = substr ( $item , strlen($arr[0]));
                }

                if(count($arr) != 2){
                    $val = $arr[0];

                    $arr = array();
                    $arr[0] = $val;
                    $arr[1] = substr ($item , strlen($arr[0]));
                }

                $arr1['value'] = trim($arr[0]);

                if (isset($arr[1])) {
                    $arr1['unit'] = trim($arr[1]);
                } else {
                    $arr1['unit'] = '';
                }

                $arr_item[] = $arr1;
            }

            $arr_groups[] = $arr_item;
        }

        //sort array by value
        $arr_groups_sorted = array();
        foreach($arr_groups as $group){
            ksort($group);
            $arr_groups_sorted[] = $group;

        }

        return $arr_groups_sorted;
    }else{
        return array();
    }

}

function qtype_numerical_get_wrongvaluepenalty() {
    global $CFG, $DB;

    $sql = "SELECT * FROM {config} WHERE name='qtype_numerical_wrongvaluepenalty'";
    $config = $DB->get_record_sql($sql);

    if(!empty($config)) {
        return $config->value;
    }else{
        return 0;
    }
}

function qtype_numerical_get_wrongunitpenalty() {
    global $CFG, $DB;

    $sql = "SELECT * FROM {config} WHERE name='qtype_numerical_wrongunitpenalty'";
    $config = $DB->get_record_sql($sql);

    if(!empty($config)) {
        return $config->value;
    }else{
        return 0;
    }
}

function qtype_numerical_prepare_units_for_student(qtype_numerical_question $question) {
    global $CFG, $DB;

    $arr_units = array();
    foreach($question->answers as $answer){ $arr_units[] = $answer->unit; }

    $all_units = qtype_numerical_prepare_units();

    //Find needed rows
//    $groups = array();
//    foreach($all_units as $key=>$group){
//        foreach($group as $item){
//            if(in_array($item['unit'], $arr_units)){
//                $groups[] = $key;
//            }
//
//        }
//    }

    //Get all rows
    $groups = array();
    foreach($all_units as $key=>$group){
        $groups[] = $key;
    }

    $groups = array_unique($groups);

    //Create form for js
    $result = array();
    foreach($groups as $group){

        $units_group = array_values($all_units[$group]);
        foreach($units_group as $key=>$item){

            $str = '';
            if($key != 0){
                $str = ' ('.$units_group[0]['value'].$units_group[0]['unit'].'='.$item['value'].$item['unit'].')';
            }

            //$result[] = $item['unit'].$str;
            $result[] = $item['unit'];
        }

    }

    $result = array_unique($result);
    $result = array_values($result);

    return $result;
}

function qtype_numerical_split_answer($value) {
    $num = $unit = null;

    $length = strlen($value);
    for ($i=0; $i <= $length; $i++) {

        $str = substr($value, 0, $length - $i);
        if (is_numeric($str)) {
            $num = floatval($str);
            $unit = substr($value, -$i, $i);
            break;
        }
    }

    $num = str_replace(' ', '', $num);
    $unit = str_replace(' ', '', $unit);

    if (empty($num)) {
        $num = null;
    }

    if (empty($unit)) {
        $unit = null;
    }

    return [$num, $unit];
}