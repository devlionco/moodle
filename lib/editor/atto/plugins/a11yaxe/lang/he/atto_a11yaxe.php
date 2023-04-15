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
 * Strings for component 'atto_a11yaxe', language 'he'.
 *
 * @package    atto_a11yaxe
 * @copyright  2021 Tamir hajaj <tamir.hajaj@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'מצא שגיאות נגישות בתוכן';
$string['nowarnings'] = 'אין שגיאות נגישות';
$string['report'] = 'דוח חריגות נגישות';
$string['imagesmissingalt'] = 'חסר תיאור של התמונה';
$string['needsmorecontrast'] = 'נדרשת ניגודיות נוספות';
$string['needsmoreheadings'] = 'חסרות כותרות';
$string['tableswithmergedcells'] = 'טבלאות עם תאים מאוחדים';
$string['tablesmissingcaption'] = 'Tables missing caption';
$string['emptytext'] = 'Empty text';
$string['entiredocument'] = 'Entire document';
$string['tablesmissingheaders'] = 'טבלאות ללא כותרות';
$string['allgood'] = 'לא נמצאו שגיאות נגישות בתוכן';
$string['dialog_showme'] = 'הצגת שגיאה';
$string['dialog_close'] = 'סיום';
$string['dialog_previous'] = 'קודם';
$string['dialog_next'] = 'הבא';
$string['there_are'] = 'זוהו';
$string['problems'] = 'בעיות';

// Rules
$string['rule__color_contrast'] = 'מוודא שהניגוד בין צבעי הרקע והחזית עונה על דרישות יחסי הניגוד של WCAG 2 AA';
$string['rule__color_contrast_desc'] = 'לאלמנטים צריך להיות ניגוד צבעים מספיק';
$string['rule__empty_table_header'] = 'מוודא שלכותרות של טבלה יש טקסט מובן';
$string['rule__empty_table_header_desc'] = 'אסור שכותרות של טבלה יהיו ריקות';
$string['rule__frame_title_unique'] = 'מוודא שלאלמנטים &lt;iframe&gt; ו-&lt;iframe/&gt; מכילים תכונת כותרת ייחודית';
$string['rule__frame_title_unique_desc'] = 'מסגרות מוכרחות להיות עם תכונת כותרת ייחודית';
$string['rule__heading_order'] = 'מוודא שסדר הכותרות נכון סמנטית';
$string['rule__heading_order_desc'] = 'הדרגות של הכותרות צריכות לגדול רק באחת';
$string['rule__image_redundant_alt'] = 'מוודא שהחלופה של התמונה לא חוזרת על עצמה בטקסט';
$string['rule__image_redundant_alt_desc'] = 'טקסט חלופי של תמונות לא אמור לחזור על עצמו בטקסט';
$string['rule__link_name'] = 'מוודא שקישורים נבדלים מהטקסט מסביב באופן שאינו נסמך על צבע';
$string['rule__link_name_desc'] = 'על קישורים להיות נבדלים מבלי להסתמך על צבע';
$string['rule__list'] = 'מוודא שרשימות בנויות נכונה';
$string['rule__list_desc'] = '&lt;ul&gt; ו-&lt;ol&gt; מוכרחים להכיל ישירות רק אלמנטים של  &lt;li&gt; , &lt;script&gt; או &lt;template&gt; ';
$string['rule__listitem'] = 'מוודא שאלמנטים של &lt;li&gt; הם בשימוש סמנטי';
$string['rule__listitem_desc'] = 'יש להכיל אלמנטים של &lt;li&gt; בתוך &lt;ul&gt; או &lt;ol&gt;';
$string['rule__no_autoplay_audio'] = 'מוודא שהאלמנטים &lt;video&gt; או &lt;audio&gt; לא מנגנים שמע אוטומטית ליותר מ-3 שניות בלי מנגנון שליטה שיעצור או ישתיק או השמע';
$string['rule__no_autoplay_audio_desc'] = 'אסור שהאלמנטים &lt;video&gt; או &lt;audio&gt; ינגנו אוטמטית';
$string['rule__p_as_heading'] = 'מוודא שטקסט דגוש, נטוי וגודל פונט לא בשימוש בעיצוב אלמנטי &lt;p&gt; ככותרת';
$string['rule__p_as_heading_desc'] = 'אסור שאלמנטי &lt;p&gt; מעוצבים ישמשו ככותרות';
$string['rule__role_img_alt'] = "מוודא שלאמנטים של ['role='img] יש טקסט חלופי";
$string['rule__role_img_alt_desc'] = "אלמנטים של ['role='img] מוכרחים להיות עם טקסט חלופי";
$string['rule__scrollable_region_focusable'] = 'מוודא שאלמנטים שיש להם תוכן בר גלילה נגישים על ידי מקלדת';
$string['rule__scrollable_region_focusable_desc'] = 'אזורי גלילה מוכרחים להיות עם נגישות של מקלדת';
$string['rule__server_side_image_map'] = 'מוודא שמפות תמונה צד-שרת לא יהיו בשימוש';
$string['rule__server_side_image_map_desc'] = 'אסור שמפות תמונה צד-שרת יהיו בשימוש';
$string['rule__svg_img_alt'] = 'מוודא שלאלמנטים של &lt;svg&gt; עם תפקיד תמונה, מסמך גרפי או סמל גרפי יש טקסט נגיש';
$string['rule__svg_img_alt_desc'] = 'אלמנטים של &lt;svg&gt; עם תפקיד של תמונה חייבים להיות עם טקסט חלופי';
$string['rule__td_has_header'] = 'מוודא שלכל תאי מידע לא-ריק ב-&lt;table&gt; גדולה מ-3X3 יש כותרות טבלה אחת או יותר';
$string['rule__td_has_header_desc'] = 'אלמנטים של &lt;td&gt; שאינם ריקים ב-&lt;table&gt; גדולה יותר מוכרחים להיות קשורים לכותרת טבלה';
$string['rule__th_has_data_cells'] = 'מוודא שלאלמנטים של &lt;th&gt; ולאלמנטים עם role=columnheader/rowheader יש תאי מידע שהם מתארים';
$string['rule__th_has_data_cells_desc'] = 'כותרות טבלה בטבלת מידע חייבים להתייחס לתאי מידע';
