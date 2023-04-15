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
 * Strings for component 'atto_a11yaxe', language 'en'.
 *
 * @package    atto_a11yaxe
 * @copyright  2021 Tamir hajaj <tamir.hajaj@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Find accessibility errors in content';
$string['nowarnings'] = 'No warnings';
$string['report'] = 'Report';
$string['imagesmissingalt'] = 'Images missing alt';
$string['needsmorecontrast'] = 'Needs more contrast';
$string['needsmoreheadings'] = 'Needs more headings';
$string['tableswithmergedcells'] = 'Tables with merged cells';
$string['tablesmissingcaption'] = 'Tables missing caption';
$string['emptytext'] = 'Empty text';
$string['entiredocument'] = 'Entire document';
$string['tablesmissingheaders'] = 'Tables missing headers';
$string['allgood'] = 'Nothing wrong, You good to go';
$string['dialog_showme'] = 'Show me';
$string['dialog_close'] = 'Close';
$string['dialog_previous'] = 'Previous';
$string['dialog_next'] = 'Next';
$string['there_are'] = 'There are';
$string['problems'] = 'problems';

// Rules
$string['rule__color_contrast'] = 'Ensures the contrast between foreground and background colors meets WCAG 2 AA contrast ratio thresholds';
$string['rule__color_contrast_desc'] = 'Elements must have sufficient color contrast';
$string['rule__empty_table_header'] = 'Ensures table headers have discernible text';
$string['rule__empty_table_header_desc'] = 'Table header text must not be empty';
$string['rule__frame_title_unique'] = 'Ensures &lt;iframe&gt; and &lt;/iframe&gt; elements contain a unique title attribute';
$string['rule__frame_title_unique_desc'] = 'Frames should have a unique title attribute';
$string['rule__heading_order'] = 'Ensures the order of headings is semantically correct';
$string['rule__heading_order_desc'] = 'Heading levels should only increase by one';
$string['rule__image_redundant_alt'] = 'Ensure image alternative is not repeated as text';
$string['rule__image_redundant_alt_desc'] = 'Alternative text of images should not be repeated as text';
$string['rule__link_name'] = 'Ensure links are distinguished from surrounding text in a way that does not rely on color';
$string['rule__link_name_desc'] = 'Links must be distinguishable without relying on color';
$string['rule__list'] = 'Ensures that lists are structured correctly';
$string['rule__list_desc'] = '&lt;ul&gt; and &lt;ol&gt; must only directly contain &lt;li&gt;, &lt;script&gt; or &lt;template&gt; elements';
$string['rule__listitem'] = 'Ensures &lt;li&gt; elements are used semantically';
$string['rule__listitem_desc'] = '&lt;li&gt; elements must be contained in a &lt;ul&gt; or &lt;ol&gt;';
$string['rule__no_autoplay_audio'] = 'Ensures &lt;video&gt; or &lt;audio&gt; elements do not autoplay audio for more than 3 seconds without a control mechanism to stop or mute the audio';
$string['rule__no_autoplay_audio_desc'] = '&lt;video&gt; or &lt;audio&gt; elements must not play automatically';
$string['rule__p_as_heading'] = 'Ensure bold, italic text and font-size is not used to style &lt;p&gt; elements as a heading';
$string['rule__p_as_heading_desc'] = 'Styled &lt;p&gt; elements must not be used as headings';
$string['rule__role_img_alt'] = "Ensures [role='img'] elements have alternate text";
$string['rule__role_img_alt_desc'] = "[role='img'] elements must have an alternative text";
$string['rule__scrollable_region_focusable'] = 'Ensure elements that have scrollable content are accessible by keyboard';
$string['rule__scrollable_region_focusable_desc'] = 'Scrollable region must have keyboard access';
$string['rule__server_side_image_map'] = 'Ensures that server-side image maps are not used';
$string['rule__server_side_image_map_desc'] = 'Server-side image maps must not be used';
$string['rule__svg_img_alt'] = 'Ensures &lt;svg&gt; elements with an img, graphics-document or graphics-symbol role have an accessible text';
$string['rule__svg_img_alt_desc'] = '&lt;svg&gt; elements with an img role must have an alternative text';
$string['rule__td_has_header'] = 'Ensure that each non-empty data cell in a &lt;table&gt; larger than 3 by 3  has one or more table headers';
$string['rule__td_has_header_desc'] = 'Non-empty &lt;td&gt; elements in larger &lt;table&gt; must have an associated table header';
$string['rule__th_has_data_cells'] = 'Ensure that &lt;th&gt; elements and elements with role=columnheader/rowheader have data cells they describe';
$string['rule__th_has_data_cells_desc'] = 'Table headers in a data table must refer to data cells';
