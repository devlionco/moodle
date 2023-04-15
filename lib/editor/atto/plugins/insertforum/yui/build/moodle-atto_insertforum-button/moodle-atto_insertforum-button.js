YUI.add('moodle-atto_insertforum-button', function (Y, NAME) {

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

/*
 * @package    atto_hvp
 * @author     Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_insertforum-button
 */

/**
 * Atto text editor insertforum plugin.
 *
 * @namespace M.atto_insertforum
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_insertforum',

    CSS = {
        INPUTFORUM: 'atto_insertforum_inputforum',
        INPUTGROUP: 'atto_insertforum_inputgroup',
        INPUTGROUPING: 'atto_insertforum_inputgrouping',
        INPUTNUMPOSTS: 'atto_insertforum_inputnumposts'
    },
    SELECTORS = {
        INPUTFORUM: '.atto_insertforum_inputforum',
        INPUTGROUP: '.atto_insertforum_inputgroup',
        INPUTGROUPING: '.atto_insertforum_inputgrouping',
        INPUTNUMPOSTS: '.atto_insertforum_inputnumposts'
    },
    TEMPLATE = '' +
        '<form class="atto_insertform">' +
            '{{get_string "select_desc" component}}<br/><br/>' +
            '{{#if forums}}' +
                '<label for="{{elementid}}_atto_insertforum_inputforums">{{get_string "forum" component}}</label>' +
                '<select class="{{CSS.INPUTFORUM}}" id="{{elementid}}_atto_insertforum_inputforums">' +
                    '{{#each forums}}' +
                        '<option value="{{id}}">{{text}}</option>' +
                    '{{/each}}' +
                '</select>' +
                '<br/>' +
                '<label for="{{elementid}}_atto_insertforum_inputgroups">{{get_string "groups" component}}</label>' +
                '<select class="{{CSS.INPUTGROUP}}" id="{{elementid}}_atto_insertforum_inputgroups">' +
                    '{{#each groups}}' +
                    '<option value="{{id}}">{{name}}</option>' +
                    '{{/each}}' +
                '</select>' +
                '<br/>' +
                '<label for="{{elementid}}_atto_insertforum_inputgroupings">{{get_string "groupings" component}}</label>' +
                '<select class="{{CSS.INPUTGROUPING}}" id="{{elementid}}_atto_insertforum_inputgroupings">' +
                    '{{#each groupings}}' +
                    '<option value="{{id}}">{{name}}</option>' +
                    '{{/each}}' +
                '</select>' +
                '<br/>' +
                '<label for="{{elementid}}_atto_insertforum_inputnumposts">{{get_string "numposts" component}}</label>' +
                '<input type="text" value="{{numposts}}" class="{{CSS.INPUTNUMPOSTS}}" id="{{elementid}}_atto_insertforum_inputnumposts"/>' +
                '<br/>' +
                '<div class="mdl-align">' +
                    '<br/>' +
                    '<button type="submit" class="submit">{{get_string "insertforum" component}}</button>' +
                '</div>' +
            '{{else}}' +
                '{{get_string "noforums" component}}' +
            '{{/if}}' +
        '</form>',
    // Insert a filter/form shortcode
    // [[forum($forumid(INT),$groupid(INT),$groupingid(INT),$nbpost(INT))]]
    IFRAMETEMPLATE = '[[forum({{forumid}},{{groupid}},{{groupingid}},{{numposts}})]]' ;

    /*
    IFRAMETEMPLATE = '' +
        '<iframe src="{{forumurl}}" class="filter_forum" id="forum_{{id}}" style="width:100%;border:0;">' + '</iframe>' +
        '<script>var filter_forum = Y.one(".filter_forum");filter_forum.on("load", function (e) {' +
        'this._node.height = this._node.contentWindow.document.body.scrollHeight + \'px\';});</script>';
    */
Y.namespace('M.atto_insertforum').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    /**
     * A reference to the dialogue content.
     *
     * @property _content
     * @type Node
     * @private
     */
    _content: null,

    initializer: function () {

        var hascapability = this.get('capability'),
            toolbarItems = [];

        if (hascapability) {

        // Add the forum button first.
        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_insertforum',
            callback: this._displayDialogue
        });
        }
    },

    /**
     * Display the forum editor.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function () {
        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();
        if (this._currentSelection === false || this._currentSelection.collapsed) {
            return;
        }

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
            focusAfterHide: true,
            focusOnShowSelector: SELECTORS.INPUTFORUM
        });

        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getDialogueContent());

        dialogue.show();
    },

    /**
     * Generates the content of the dialogue.
     *
     * @method _getDialogueContent
     * @return {Node} Node containing the dialogue content
     * @private
     */
    _getDialogueContent: function () {
        var template = Y.Handlebars.compile(TEMPLATE);
        //debugger;
        this._content = Y.Node.create(template({
            component: COMPONENTNAME,
            forums: this.get('forums'),
            groups: this.get('groups'),
            groupings: this.get('groupings'),
            elementid: 'id',
            numposts: 1,
            CSS: CSS
        }));

        if (this._content.one('.submit')) {
            this._content.one('.submit').on('click', this._insertforum, this);
        }

        return this._content;
    },

    /**
     * The forum was inserted, so make changes to the editor source.
     *
     * @method _insertforum
     * @param {EventFacade} e
     * @private
     */
    _insertforum: function (e) {
        var inputforum,forumid,
            inputgroup,groupid,
            inputgrouping,groupingid,
            inputnumposts,numposts,
            host = this.get('host');

        e.preventDefault();

        // Hide the dialogue.
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        inputforum = this._content.one('#id_atto_insertforum_inputforums');
        //text = input.get('text');
        forumid = inputforum.get('value');

        inputgroup = this._content.one('#id_atto_insertforum_inputgroups');
        groupid = inputgroup.get('value');

        inputgrouping = this._content.one('#id_atto_insertforum_inputgroupings');
        groupingid = inputgrouping.get('value');

        inputnumposts = this._content.one('#id_atto_insertforum_inputnumposts');
        numposts = inputnumposts.get('value');

        host.focus();
        if (forumid !== '') {
            var template = Y.Handlebars.compile(IFRAMETEMPLATE);
            var imagehtml = template({
                forumid: forumid,
                groupid: groupid,
                groupingid: groupingid,
                numposts: numposts
                //forumurl: M.cfg.wwwroot + '/mod/forum/view.php?id=' + value
            });

            host.insertContentAtFocusPoint(imagehtml);

            this.markUpdated();
        }

        this.getDialogue({
            focusAfterHide: null
        }).hide();
    }

}, {
    ATTRS: {
        /**
         * The list of forums to display.
         *
         * @attribute forums
         * @type array
         * @default {}
         */
        forums: {
            value: []
        },
        groups: {
            value: []
        },
        groupings: {
            value: []
        },
        numposts: {
            value: []
        },
        capability: {
            value: []
        }

    }
});


}, '@VERSION@');
