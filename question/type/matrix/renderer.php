<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/matrix/libs/config.php');

/**
 * Generates the output for matrix questions.
 */
class qtype_matrix_renderer extends qtype_with_combined_feedback_renderer
{

    /**
     * Generate the display of the formulation part of the question. This is the
     * area that contains the quetsion text, and the controls for students to
     * input their answers. Some question types also embed bits of feedback, for
     * example ticks and crosses, in this area.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options)
    {

        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();

        $table = new html_table();
        $table->attributes['class'] = 'matrix';

        // mod_ND : BEGIN
        if (config::allow_dnd_ui() && $question->use_dnd_ui) {
            $table->attributes['class'] .= ' uses_dndui';
        }
        // mod_ND : END

        $table->head = array();
        $table->head[] = '';

        $order = $question->get_order($qa);

        foreach ($question->cols as $col) {
            $table->head[] = self::matrix_header($col);
        }

        if ($options->correctness) {
            $table->head[] = get_string('correctness_answer', 'qtype_matrix');
        }

        foreach ($order as $rowid) {

            $row = $question->rows[$rowid];
            $row_data = array();
            $row_data[] = self::matrix_header($row);
            foreach ($question->cols as $col) {
                $key = $question->key($row, $col);
                $cell_name = $qa->get_field_prefix() . $key;

                $is_readonly = $options->readonly;
                $is_checked = $question->response($response, $row, $col);

                $feedback = false;
                if ($options->correctness) {
                    $weight = $question->weight($row, $col);
                    $feedback = $this->if_feedback_border($weight);
                }

                if ($question->multiple) {
                    $cell = self::checkbox($cell_name, $is_checked, $is_readonly, $feedback);
                } else {
                    $cell = self::radio($cell_name, $col->id, $is_checked, $is_readonly, $feedback);
                }
                $row_data[] = $cell;
            }

            if ($options->correctness) {
                $row_grade = $question->grading()->grade_row($question, $row, $response);
                $feedback = $row->feedback['text'];
                $feedback = strip_tags($feedback) ? $feedback : '';
                $row_data[] = $this->feedback_image($row_grade) . $feedback;
            }
            $table->data[] = $row_data;

            //$row_index++;
        }
        $question_text = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $question_text, array('class' => 'question_text'));
        $result .= html_writer::table($table, true);
        return $result;
    }

    public static function matrix_header($header)
    {
        $text = $header->shorttext;

        $description = $header->description['text'];
        if (strip_tags($description)) {
            $description = preg_replace('-^<p>-', '', $description);
            $description = preg_replace('-</p>$-', '', $description);
            $description = '<span class="description" >' . format_text($description) . '</span>';
        } else {
            $description = '';
        }

        return '<span class="title">' . format_text($text) . '</span>' . $description;
    }

    protected static function checkbox($name, $checked, $readonly, $feedback)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';

        if($feedback){
        return <<<EOT
        <div style="display: inline-flex;
            border: 3px solid green;
            align-items: center;
            border-radius: 5px;
            justify-content: center;
        ">
            <input type="checkbox" name="$name" style="margin: 0" $checked $readonly />
        </div>
EOT;
        }

        return <<<EOT
        <input type="checkbox" name="$name" $checked $readonly />
EOT;
    }

    protected static function radio($name, $value, $checked, $readonly, $feedback)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';

        if($feedback){
            return <<<EOT
        <div style="display: inline-flex;
            border: 3px solid green;
            border-radius: 30px;
            align-items: center;
            justify-content: center;">
            <input type="radio" name="$name" style="margin: 0" value="$value" $checked $readonly />
        </div>
EOT;
        }

        return <<<EOT
        <input type="radio" name="$name" value="$value" $checked $readonly />
EOT;
    }

    /**
     * Return an appropriate icon (green tick, red cross, etc.) for a grade.
     * @param float $fraction grade on a scale 0..1.
     * @param bool $selected whether to show a big or small icon. (Deprecated)
     * @return string html fragment.
     */
    protected function if_feedback_border($fraction) {

        if(question_state::graded_state_for_fraction($fraction)->is_correct()){
            return true;
        }

        return false;
    }

}
