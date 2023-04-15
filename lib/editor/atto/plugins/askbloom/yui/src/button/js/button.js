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
 * @package    atto_askbloom
 * @copyright  2023 Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto-askbloom-button
 */

/**
 * Atto text editor askbloom plugin.
 *
 * @namespace M.atto_link
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var LOGNAME = 'atto_askbloom';

Y.namespace('M.atto_askbloom').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    initializer: function() {
        if (this.get('disabled')) {
            return;
        }

        this.addButton({
            iconComponent: LOGNAME,
            icon: 'pyramid',
            callback: this._displayDialogue
        });
    },

    /**
     * Display the askbloom.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function(e) {
        e.preventDefault();
        var host = this.get('host');

        host.editor.focus();

        // Store the current selection.
        this._currentSelection = host.getSelection();
        if (this._currentSelection === false) {
            return;
        }

        var selection = host.get('elementid');
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', LOGNAME),
            width: '800px',
            focusAfterHide: true
        });

        var iframe = Y.Node.create('<iframe></iframe>');
        // We set the height here because otherwise it is really small. That might not look
        // very nice on mobile devices, but we considered that enough for now.
        iframe.setStyles({
            height: '700px',
            border: 'none',
            width: '100%'
        });

        iframe.setAttribute('src',M.cfg.wwwroot+'/lib/editor/atto/plugins/askbloom/dialog.php?type=atto&id='+selection+'editable');

        dialogue.set('bodyContent', iframe)
                .show();

        window.addEventListener('message', function(event) {
                if (event.data.result) {
                    document.getElementById(event.data.id).append(event.data.result);
                    delete event.data.result;
                    delete event.data.id;
                    var arr = document.getElementsByClassName('closebutton');
                    arr.forEach(function callback(currentValue) {
                        currentValue.click();
                    });
                }
            },
            false);

        this.markUpdated();
    },
}
);
