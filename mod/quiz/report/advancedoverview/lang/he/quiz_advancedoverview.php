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
 * @package     quiz_advancedoverview
 * @category    string
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Advanced overview';
$string['advancedoverview'] = 'Advanced overview';
$string['privacy:metadata'] = 'Advanced overview does not store any personal data';
$string['separategroups'] = 'קבוצות נפרדות';
$string['allparticipants'] = 'כל המשתתפים';
$string['classview'] = 'תצוגה כיתתית';

$string['anonymousmodeoff'] = 'מצב אנונימי כבוי';
$string['anonymousmodeon'] = 'מצב אנונימי מופעל';
$string['editingquestions'] = 'עריכת שאלות';
$string['preview'] = 'תצוגה מקדימה';

$string['moreactions'] = 'עוד פעולות';
$string['stopresponse'] = 'עצירת מענה כיתתית';
$string['reportregrade'] = 'חישוב ציונים מחדש';
$string['editingtask'] = 'עריכת משימה';
$string['studentsnograde'] = 'תלמידים לא קיבלו ציון';
$string['openquestionwaiting'] = 'שאלות פתוחות ממתינות להשלמת ציון';
$string['wrong'] = 'שגו';
$string['raiseflag'] = 'הרימו דגל';
$string['usehint'] = 'השתמשו ברמז';
$string['usechat'] = 'פנו בצ׳אט';
$string['answered'] = 'ענו';

$string['studentsintotal'] = 'סכ"ה {$a} תלמידים';

$string['notstarted'] = 'לא התחילו';
$string['inprogress'] = 'בתהליך';
$string['finished'] = 'הסתיים';
$string['submitted'] = 'הגישו';
$string['all'] = 'כולם';
$string['late'] = 'באיחור';

$string['anonymous_firstname'] = 'Anonymous';
$string['anonymous_lastname'] = 'Username';

$string['viewingprofile'] = 'צפייה בפרויפל';
$string['passwordreset'] = 'אתחול סיסמא';
$string['loginasthisstudent'] = 'התחבר כתלמיד זה';
$string['allcoursereport'] = 'דוח קורס מלא';
$string['courseobservationreport'] = 'דוח צפיה בקורס';
$string['sendingmessage'] = 'שליחת הודעה';
$string['recalculategrades'] = 'חישוב ציונים מחדש';
$string['advancedoverviewreport'] = 'Advanced Overview Report';
$string['deletingattempts'] = 'מחיקת נסיונות';
$string['selectanaction'] = 'בחרו פעולה…';
$string['selectedstudents'] = 'נבחרו {$a} תלמידים';
$string['closingresponseattempts'] = 'סגירת נסיונות מענה';

// Table titles.
$string['fullname'] =
        '<span class="fname pl-2 pr-3" data-sort="asc">שם פרטי</span> / <span class="lname pl-2 pr-3" data-sort="asc">שם משפחה</span>';
$string['state'] = 'מצב';
$string['attempt_number'] = 'ניסיון';
$string['grade'] = 'ציון';
$string['starttime'] = 'הותחל ב';
$string['endtime'] = 'הושלם ב';
$string['duration'] = 'משך הזמן';

$string['question'] = 'שאלה מס';
$string['searchstudent'] = 'חיפוש תלמיד...';
$string['scoredisplay'] = 'הצגת ניקוד';
$string['extendedview'] = 'תצוגה מורחבת';
$string['scoreranges'] = 'טווחי ציונים';
$string['responseattempts'] = 'נסיונות מענה';
$string['responseattempts'] = 'נסיונות מענה';
$string['attempt1'] = 'נסיון 1';
$string['attempt2'] = 'נסיון 2';
$string['attempt3andmore'] = 'נסיון 3 ומעלה';
$string['lastattempt'] = 'נסיון אחרון';
$string['numofquestions'] = 'לפי שאלות ';
$string['numofstudents'] = 'לפי תלמידים ';

$string['averagegrade'] = 'ציון ממוצע';
$string['highestscore'] = 'הציון הגבוה ביותר';
$string['lowestscore'] = 'הציון הנמוך ביותר';
$string['classstatus'] = 'סטטוס כיתתי';
$string['sendingmassage'] = 'שליחת הודעה';
$string['scoredistribution'] = 'התפלגות ציונים';

$string['empty'] = 'אין נתונים להצגה';
$string['anon_user'] = 'תלמיד/ה';
$string['areyoushure'] = 'האם לבצע את פעולה?';
$string['execute'] = 'לבצע';
$string['selecteditemswrong'] = 'הבחירה שלך לא מתימה לפעולה הזות';
$string['filter'] = 'סינון';
$string['cleareverything'] = 'נקה הכל';

$string['task'] = 'Task advanced overview';
$string['cachedef_advancedoverview'] = 'Advanced overview';

$string['regradingattemptxofy'] = 'חישוב מחדש של ניסיון ({$a->done}/{$a->count})';
$string['submitted'] = 'הגישו';
$string['notsubmitted'] = 'בתהליך';
$string['notstarted'] = 'לא התחילו';
$string['attempts'] = 'מספר ניסיונות מענה';
$string['max_grade'] = 'הציון הגבוה בכיתה';
$string['min_grade'] = 'הציון הנמוך בכיתה';
