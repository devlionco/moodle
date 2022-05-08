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
 * Provides an in browser PDF editor.
 *
 * @module moodle-assignfeedback_editpdf-editor
 */

/**
 * Class representing a list of reach text.
 *
 * @namespace M.assignfeedback_editpdf
 * @class absqeditor
 * @constructor
 * @extends M.core.dialogue
 */
var ABSQEDITOR = function(config) {
    config.draggable = true;
    config.centered = true;
    config.width = 'auto';
    config.visible = false;
    config.headerContent = M.util.get_string('absqeditor', 'assignfeedback_editpdf');
    config.footerContent = '';
    config.closeButton = false;
    ABSQEDITOR.superclass.constructor.apply(this, [config]);
};

var ABSQEDITORNAME = "absqeditor";


Y.extend(ABSQEDITOR, M.core.dialogue, {
    /**
     * Initialise the menu.
     *
     * @method initializer
     * @return void
     */
    editor: null,
    initializer: function(config) {
        var editorr,
            container,
            textarea,
            bb;
        this.editor = config.editor || null;
        bb = this.get('boundingBox');
        bb.addClass('assignfeedback_editpdf_absqeditor');
        editorr = this.get('editor');
        container = Y.Node.create('<div/>');
        textarea = Y.one('#editorcontainer');
        textarea.removeClass('hidden');
        container.append(textarea);

        Y.one('[name="savechanges"]').on('click', this.removeeditor);
        Y.one('[name="saveandshownext"]').on('click', this.removeeditor);
        // Set the body content.
        this.set('bodyContent', container);
        ABSQEDITOR.superclass.initializer.call(this, config);
        this.addButton({
            name: 'confirm',
            label: M.util.get_string('confirm', 'moodle'),
            action: function(e) {
                e.preventDefault();
                this.hide();
            },
            classNames: 'btn btn-primary',
            section: Y.WidgetStdMod.FOOTER
        });
        this.addButton({
            name: 'cancel',
            label: M.util.get_string('cancel', 'moodle'),
            action: function(e) {
                e.preventDefault();
                Y.one('#absq_editor').set('value', ' ');
                this.hide();
            },
            classNames: 'btn btn-secondary',
            section: Y.WidgetStdMod.FOOTER
        });

    },
    removeeditor: function () {
        if (Y.one(".assignfeedback_editpdf_absqeditor")) {
            Y.one(".assignfeedback_editpdf_absqeditor").remove();
        }
    }
},{
    NAME: ABSQEDITORNAME,
    ATTRS: {
        /**
         * The editor this search window is attached to.
         *
         * @attribute editor
         * @type M.assignfeedback_editpdf.editor
         * @default null
         */
        editor: {
            value: null
        }

    }
});
Y.Base.modifyAttrs(ABSQEDITOR, {
    /**
     * Whether the widget should be modal or not.
     *
     * Moodle override: We override this for commentsearch to force it always true.
     *
     * @attribute Modal
     * @type Boolean
     * @default true
     */
    modal: {
        getter: function() {
            return true;
        }
    }
});
M.assignfeedback_editpdf = M.assignfeedback_editpdf || {};
M.assignfeedback_editpdf.absqeditor = ABSQEDITOR;
