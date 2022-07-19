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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     gradingform_absquestion
 * @category    string
 * @copyright   Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'בדיקת שאלות רוחבית';
$string['buttontitle'] = 'טבלה ניקוד שאלות';

$string['title'] = 'ממשק הגדרות שאלות ותתי שאלות';
$string['general_settings'] = 'הגדרות כלליות';
$string['maximum_grade'] = 'ציון מרבי למטלה';
$string['total_questions'] = 'סה"כ שאלות במבחן';
$string['total_questions_groups'] = 'מקבצים לבחירת שאלות';
$string['grading_methods'] = 'שיטת ניקוד';
$string['question'] = 'שאלה';
$string['questions'] = 'שאלות';
$string['cancel'] = 'לבטל';
$string['sub_question'] = 'סעיפים';
$string['max_points'] = 'ניקוד מרבי';
$string['bonus'] = 'בונוס';
$string['question_groups'] = 'שיוך שאלה למקבץ לבחירת שאלות';
$string['n_of_sub_questions'] = 'סעיפים שאלה';
$string['branch'] = 'סעיף';
$string['points'] = 'נקודות';
$string['group'] = 'קבוצה';
$string['from'] = 'מ';
$string['total'] = 'סה"כ';
$string['total_bonuses_questions'] = 'סה"כ שאלות בונוסים';
$string['total_question_to_choose'] = 'סה"כ שאלה לבחירה';
$string['total_max_grade'] = 'סה"כ ציון מרבי';
$string['err_sub_sum'] = 'נקודות המשנה לשאלות חייבות להיות שוות לשאלה';
$string['err_integer'] = 'חייב להיות מספר שלם, וגדול מ-0 וקטן מהציון מרבי';
$string['err_integer_number'] = 'חייב להיות מספר שלם הגדול מ-0 וקטן מנקודה';
$string['submit_form'] = 'שמירת שינויים והפעלת מערך הבדיקה במטלה';
$string['save_form'] = 'שמירת שינויים והמשך עריכה';
$string['cancel_changes'] = 'לבטל שינויים';
$string['error'] = 'שגיאה';
$string['err_total_max_grade'] = 'הציון המרבי הכולל לא יעלה על הציון המרבי';
$string['err_grading_method'] = 'אנא בחר שיטת ניקוד';
$string['err_submit_modal'] = 'הניקוד לא מסתכם לציון הכולל למטלה (אם יישאר כך, יחושב כמשקלים בהתאמה)';
$string['actions'] = 'פעולות';

$string['grading_method_0'] = 'בחר/י…';
$string['grading_method_1'] = 'צבירת ניקוד';
$string['grading_method_2'] = 'הורדת ניקוד';
$string['scoring_lower'] = 'הורדת ניקוד -';
$string['total_q_groups_0'] = 'בחר/י…';
$string['total_q_groups'] = '{$a}';
$string['total_q_0'] = 'בחר/י…';
$string['total_q'] = '{$a} שאלות';
$string['number_of_subq_0'] = 'בחר/י…';
$string['number_of_subq'] = 'נבחרו {$a} שאלות משנה';
$string['saved_successfully'] = 'נשמר בהצלחה';

$string['add'] = 'הוסף הערות לבודקים';
$string['manage_comments'] = 'ניהול הערות';
$string['must_be_number'] = 'חייב להיות מספר יותר מ-0';

// Comments.
$string['lowergrade'] = 'הורדת ניקוד';
$string['accumulate_grade'] = 'צבירת ניקוד';
$string['view_comment_on_each_question'] = 'הצגת ההערה בכל השאלות';
$string['add_comment'] = 'הוספת הערה חדשה';
$string['save'] = 'לשמור';
$string['edit'] = 'לערוך';
$string['delete'] = 'למחוק';
$string['are_you_sure'] = 'האם אתה בטוח שברצונך למחוק את התגובה הזו?';
$string['yes'] = 'כן';
$string['no'] = 'לא';
$string['global'] = 'מוצג בכל השאלות';

// Help icons.
$string['maximum_grade_desc'] = 'ציון מרבי מגיע מהגדרות המטלה';
$string['total_questions_desc'] = 'סה"כ שאלות במבחן';
$string['total_questions_groups_desc'] = 'אם יש בחירת שאלות, יש להגדיר מקבצי שאלות, לכל מקבץ ניתן להגדיר על כמה שאלות יש לענות. בכל מקבץ השאלות צריכות להיות שוות ערך בניקוד.';
$string['grading_methods_desc'] = 'צבירת ניקוד: ציון מתחיל מ 0 ומוסיפים נקודות
הורדת ניקוד: ציון מתחיל ממלוא הניקוד על השאלה ומורידים נקודות
';
$string['question_desc'] = 'מס. שאלה';
$string['sub_question_desc'] = 'ניתן להוסיף סעיפים לשאלה';
$string['max_points_desc'] = 'ניקוד מרבי בשאלה וסעיפים';
$string['bonus_desc'] = 'בונוס - הוא סוג שאלה שמשלים את הציון עם נקודות';
$string['question_groups_desc'] = 'שיוך שאלה למקבץ לבחירת שאלות';
$string['actions_desc'] = 'ניתן להוסיף הערות לשאלה';
$string['info'] = 'הנחייה לבודקים';
$string['info_desc'] = 'הנחייה לבודקים שתופיע בדף ציון';

$string['draft_mode'] = 'מצב טיוטה';
$string['ready_to_submit'] = 'מוכן לשליחה';
$string['add_a_note'] = 'הוספת הערה';

$string['freese_warning'] = 'קיים הערות למטלה הזאת , לא ניתן לשנות את הטבלה';
$string['warning'] = 'אַזהָרָה';
$string['warning_max_grade'] = 'ציון כל הערות צריך להיות פחות או שווה לציון מקסימלי';
