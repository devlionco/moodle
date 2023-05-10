YUI.add('moodle-atto_bdi-button', function (Y, NAME) {

/*
 * @package    atto_bdi
 * @copyright  2022 The Open University of Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_bdi-button
 */

/**
 * Atto text editor bdi plugin.
 *
 * @namespace M.atto_bdi
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

Y.namespace('M.atto_bdi').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    initializer: function() {
        var bdi;

        this.addButton({
            callback: this._togglebdi,
            icon: 'icon',
            iconComponent: 'atto_bdi',
            buttonName: bdi,
            inlineFormat: false, // Mark as false, only for the ability to show a message when no text is selected.

            // Watch the following tags and add/remove highlighting as appropriate:
            tags: 'span[dir="auto"], bdi'
        });
    },
    /**
     * Toggle the bdi setting.
     *
     * @method _togglebdi
     * @param {EventFacade} e
     */
    _togglebdi: function() {
        var host = this.get('host'),
            selection = host.getSelection(),
            bdiApplier;
        if (selection === false) {
            return;
        }
        if (selection[0].collapsed) {
            host.showMessage(M.util.get_string('selectionrequired', 'atto_bdi'), 'warning', 5000);
            return;
        }
        bdiApplier = window.rangy.createClassApplier("bf-editor-bdi");
        host.changeToCSS('bdi,span[dir=auto]', 'bf-editor-bdi');
        bdiApplier.toggleSelection();

        host.editor.all('.bf-editor-bdi').each(function(node) {
            node.removeClass('bf-editor-bdi');
            node.setAttribute('dir', 'auto');
        }, this);
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
