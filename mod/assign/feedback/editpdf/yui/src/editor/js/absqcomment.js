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
 * Class representing a list of absqcomments.
 *
 * @namespace M.assignfeedback_editpdf
 * @class absqcomment
 * @param M.assignfeedback_editpdf.editor editor
 * @param Int gradeid
 * @param Int pageno
 * @param Int x
 * @param Int y
 * @param Int width
 * @param String colour
 * @param String rawtext
 */
var ABSQCOMMENT = function(editor, gradeid, pageno, x, y, width, colour, rawtext, points, sequence, questionid, commentid) {

    /**
     * Reference to M.assignfeedback_editpdf.editor.
     * @property editor
     * @type M.assignfeedback_editpdf.editor
     * @public
     */
    this.editor = editor;

    /**
     * Comment id
     * @property commentid
     * @type Int
     * @public
     */
    this.commentid = commentid || 0;

    /**
     * Grade id
     * @property gradeid
     * @type Int
     * @public
     */
    this.gradeid = gradeid || 0;

    /**
     * X position
     * @property x
     * @type Int
     * @public
     */
    this.x = parseInt(x, 10) || 0;

    /**
     * Y position
     * @property y
     * @type Int
     * @public
     */
    this.y = parseInt(y, 10) || 0;

    /**
     * absqcomment width
     * @property width
     * @type Int
     * @public
     */
    this.width = parseInt(width, 10) || 0;

    /**
     * absqcomment rawtext
     * @property rawtext
     * @type String
     * @public
     */
    this.rawtext = rawtext || '';

    /**
     * absqcomment points
     * @property points
     * @type Int
     * @public
     */
    this.points = parseInt(points, 10) || 0;

    /**
     * absqcomment sequence
     * @property sequence
     * @type Int
     * @public
     */
    this.sequence = parseInt(sequence, 10) || 0;

     /**
     * absqcomment question id
     * @property questionid
     * @type Int
     * @public
     */
    this.questionid = parseInt(questionid, 10) || 0;

    /**
     * absqcomment page number
     * @property pageno
     * @type Int
     * @public
     */
    this.pageno = pageno || 0;

    /**
     * absqcomment background colour.
     * @property colour
     * @type String
     * @public
     */
    this.colour = colour || '0';

    /**
     * Reference to M.assignfeedback_editpdf.drawable
     * @property drawable
     * @type M.assignfeedback_editpdf.drawable
     * @public
     */
    this.drawable = false;

    /**
     * Boolean used by a timeout to delete empty absqcomments after a short delay.
     * @property deleteme
     * @type Boolean
     * @public
     */
    this.deleteme = false;

    /**
     * Reference to the link that opens the menu.
     * @property menulink
     * @type Y.Node
     * @public
     */
    this.menulink = null;

    /**
     * Reference to the dialogue that is the context menu.
     * @property menu
     * @type M.assignfeedback_editpdf.dropdown
     * @public
     */
    this.menu = null;

    /**
     * Clean a absqcomment record, returning an oject with only fields that are valid.
     * @public
     * @method clean
     * @return {}
     */
    this.clean = function() {
        return {
            gradeid: this.gradeid,
            x: parseInt(this.x, 10),
            y: parseInt(this.y, 10),
            width: parseInt(this.width, 10),
            rawtext: this.rawtext,
            pageno: parseInt(this.pageno, 10),
            points: parseInt(this.points, 10),
            colour: this.colour,
            sequence: parseInt(this.sequence, 10),
            questionid: parseInt(this.questionid, 10),
            commentid: parseInt(this.commentid, 10),
        };
    };

    // chack if string is number
    this.isNumeric = function(str) {
        if (typeof str == "number") {return true;}
        if (typeof str != "string") {return false;}
        return !isNaN(str) && !isNaN(parseFloat(str));
    };

    /**
     * Draw a absqcomment.
     * @public
     * @method draw
     * @return M.assignfeedback_editpdf.drawable
     */
    this.draw = function(editnode) {

        // eslint-disable-next-line no-console
        console.log('add component');

        var drawable = new M.assignfeedback_editpdf.drawable(this.editor),
            drawingcanvas = this.editor.get_dialogue_element(SELECTOR.DRAWINGCANVAS),
            container,
            node,
            menu,
            position,
            scrollheight,
            textarea;

        // custom change rawtext
        var absqEdBtn = Y.one('#page-mod-assign-grader .absqeditorbutton');
        if (absqEdBtn) {
            var commentId = absqEdBtn.getAttribute('data-id');
            var commentSequence = absqEdBtn.getAttribute('data-sequence');
            var commentPoint = absqEdBtn.getAttribute('data-points');
            var commentText = absqEdBtn.getAttribute('data-text');
            var questionIdAttr = +absqEdBtn.getAttribute('data-questionid');

            if (!this.questionid && questionIdAttr){
                this.questionid = questionIdAttr;
            }

            if (!this.points) {
                this.points = commentPoint || 0;
            }

            if (commentSequence && this.sequence === 0) {
                this.sequence = +commentSequence;
            }

            if (commentId && this.commentid === 0){
                this.commentid = +commentId;
            }

            // eslint-disable-next-line no-console
            console.log('nnn ', this.commentid, commentId);

            if (commentId && !this.rawtext){
                if (document.getElementsByClassName('dir-rtl').length !== 0) {
                    this.rawtext = '<p dir="rtl" style="text-align: right;">' + commentText + '</p>';
                } else {
                    this.rawtext = '<p dir="ltr" style="text-align: left;">' + commentText + '</p>';
                }
            }

            var usedpointEl = Y.one('#usedpoint_' + this.questionid);
            var usedpoint = +usedpointEl._node.innerHTML;
            var absqBtn = Y.one('body#page-mod-assign-grader .absqeditorbutton');
            var absqBtnValue = absqBtn._node.attributes['aria-pressed'].nodeValue;

            if (this.isNumeric(usedpoint) &&
                this.isNumeric(this.points) &&
                absqBtnValue === "true"
                ) {
                usedpointEl._node.innerHTML = +this.points + usedpoint;
            }
        }

        var colorArr = [
            "#CFEFCF",
            "#262949",
            "#700460",
            "#A02C5D",
            "#EC0F47",
            "#EE6B3B",
            "#FBBF54",
            "#ABD96D",
            "#15C286",
            "#087353",
            "#65aee7",
        ];
        var colorNow = this.sequence <= 10 ? colorArr[this.sequence] : colorArr[0];

        menu = Y.Node.create('<a href="#"><img src="' + M.util.image_url('t/contextmenu', 'core') + '"/></a>');
        // // Lets add a contenteditable div.
        node = editnode || Y.Node.create('<div/>');
        node.addClass('absqcomment');
        // eslint-disable-next-line max-len
        container = Y.Node.create('<div data-questionid="'+this.questionid+'" data-points="'+this.points+'" style="border:5px solid '+colorNow+';" class="absqcommentdrawable" data-sequence="'+this.sequence+'" />');
        // questionTitle = Y.Node.create('<span class="test"> Question </span>');
        this.menulink = menu;
        if (this.rawtext.replace(/^\s+|\s+$/g, "") === '') {
            textarea = Y.one('#absq_editor');
            this.rawtext = textarea.get('value');
        }
        menu.setAttribute('tabindex', '0');
        if (!this.editor.get('readonly')) {
            container.append(menu);
            // container.append(questionTitle);
        } else {
            node.setAttribute('readonly', 'readonly');
        }
        if (this.width < 200) {
            this.width = 200;
        }
        node.set('innerHTML', this.rawtext);
        Y.use('mathjax', function() {
            window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, node.getDOMNode()]);
        });
        scrollheight = node.get('scrollHeight');
        node.setStyles({
            // 'height': scrollheight + 'px'
        });
        position = this.editor.get_window_coordinates(new M.assignfeedback_editpdf.point(this.x, this.y));
        node.setStyles({
            width: this.width + 'px'
        });
        container.append(node);
        drawingcanvas.append(container);
        container.setStyle('position', 'absolute');
        container.setX(position.x);
        container.setY(position.y);
        drawable.store_position(container, position.x, position.y);

        // Bind events only when editing.
        if (!this.editor.get('readonly')) {
            // Pass through the event handlers on the div.
            node.on('gesturemovestart', this.editor.edit_start, null, this.editor);
            node.on('gesturemove', this.editor.edit_move, null, this.editor);
            node.on('gesturemoveend', this.editor.edit_end, null, this.editor);
        }
        drawable.nodes.push(container);

        this.attach_events(node, menu);



        this.drawable = drawable;

        node.focus();
        this.width = parseInt(node.getStyle('width'), 10);

        // Trim.
        // if (this.rawtext.replace(/^\s+|\s+$/g, "") === '') {
        //     // Delete empty absqcomments.
        //     this.deleteme = true;
        //     Y.later(400, this, this.delete_absqcomment_later);
        //     if (document.getElementsByClassName('dir-rtl').length !== 0) {
        //         Y.one('#absq_editoreditable').set('innerHTML', '<p dir="rtl" style="text-align: right;"><br></p>');
        //     } else {
        //         Y.one('#absq_editoreditable').set('innerHTML', '<p dir="ltr" style="text-align: left;"><br></p>');
        //     }
        //     if (!this.editor.absqeditorwindow) {
        //         this.editor.absqeditorwindow = new M.assignfeedback_editpdf.absqeditor({
        //             editor: this
        //         });
        //     }
        // }
        // node.active = false;
        // if (this.rawtext.replace(/^\s+|\s+$/g, "") !== '') {
        //     if (editnode) {
        //         this.editor.save_current_page();
        //     }
        //     this.drawable = drawable;
        //     if (textarea) {
        //         textarea.set('value', ' ');
        //         if (document.getElementsByClassName('dir-rtl').length !== 0) {
        //             Y.one('#absq_editoreditable').set('innerHTML', '<p dir="rtl" style="text-align: right;"><br></p>');
        //         } else {
        //             Y.one('#absq_editoreditable').set('innerHTML', '<p dir="ltr" style="text-align: left;"><br></p>');
        //         }
        //     }
        // }

        return drawable;
    };

    /**
     * Delete an empty absqcomment if it's menu hasn't been opened in time.
     * @method delete_absqcomment_later
     */
    this.delete_absqcomment_later = function() {
        if (this.deleteme) {
            this.remove();
        }
    };

    /**
     * absqcomment nodes have a bunch of event handlers attached to them directly.
     * This is all done here for neatness.
     *
     * @protected
     * @method attach_absqcomment_events
     * @param node - The Y.Node representing the absqcomment.
     * @param menu - The Y.Node representing the menu.
     */
    this.attach_events = function(node, menu) {
        var container = node.ancestor('div');
        // eslint-disable-next-line no-console
        console.log('start move', container);

        if (!this.editor.get('readonly')) {
            // eslint-disable-next-line no-console
            console.log('start move');

            // For delegated event handler.
            menu.setData('absqcomment', this);

            node.on('gesturemovestart', function(e) {
                if (editor.currentedit.tool === 'select') {
                    e.preventDefault();
                    // eslint-disable-next-line no-console
                    console.log('start move');

                    node.setData('offsetx', e.clientX - container.getX());
                    node.setData('offsety', e.clientY - container.getY());
                }
            });
            node.on('gesturemove', function(e) {
                if (editor.currentedit.tool === 'select') {
                    var x = e.clientX - node.getData('offsetx'),
                        y = e.clientY - node.getData('offsety'),
                        newlocation,
                        windowlocation,
                        bounds;

                    if (node.getData('clicking') !== true) {
                        node.setData('clicking', true);
                    }

                    newlocation = this.editor.get_canvas_coordinates(new M.assignfeedback_editpdf.point(x, y));
                    bounds = this.editor.get_canvas_bounds(true);
                    bounds.x = 0;
                    bounds.y = 0;

                    bounds.width -= 24;
                    bounds.height -= 24;
                    // Clip to the window size - the comment icon size.
                    newlocation.clip(bounds);

                    this.x = newlocation.x;
                    this.y = newlocation.y;

                    windowlocation = this.editor.get_window_coordinates(newlocation);
                    container.setX(windowlocation.x);
                    container.setY(windowlocation.y);
                    this.drawable.store_position(container, windowlocation.x, windowlocation.y);
                }
            }, null, this);
            this.menu = new M.assignfeedback_editpdf.absqcommentmenu({
                buttonNode: this.menulink,
                absqcomment: this
            });
        }
    };

    /**
     * Delete a absqcomment.
     * @method remove
     */
    this.remove = function() {

        // eslint-disable-next-line no-console
        console.log('remove component');

        var i = 0;
        var absqcomments;

        absqcomments = this.editor.pages[this.editor.currentpage].absqcomments;
        for (i = 0; i < absqcomments.length; i++) {
            if (absqcomments[i] === this) {
                absqcomments.splice(i, 1);
                this.drawable.erase();
                this.editor.save_current_page();

                // change current points
                var usedpointEl = Y.one('#usedpoint_' + this.questionid);
                var usedpoint = +usedpointEl._node.innerHTML;
                if (usedpoint && +this.points){
                    usedpointEl._node.innerHTML = usedpoint - +this.points;
                }

                return;
            }
        }
    };

    /**
     * Draw the in progress edit.
     *
     * @public
     * @method draw_current_edit
     * @param M.assignfeedback_editpdf.edit edit
     */
    this.draw_current_edit = function(edit) {
        var bounds = new M.assignfeedback_editpdf.rect(),
            drawable = new M.assignfeedback_editpdf.drawable(this.editor),
            drawingregion = this.editor.get_dialogue_element(SELECTOR.DRAWINGREGION),
            node,
            position;

        bounds.bound([edit.start, edit.end]);
        position = this.editor.get_window_coordinates(new M.assignfeedback_editpdf.point(bounds.x, bounds.y));

        node = Y.Node.create('<div/>');
        node.setStyles({
            'position': 'absolute',
            'display': 'inline-block',
            'width': bounds.width,
            'height': bounds.height,
            'backgroundSize': '100% 100%'
        });

        drawingregion.append(node);
        node.setX(position.x);
        node.setY(position.y);
        drawable.store_position(node, position.x, position.y);

        drawable.nodes.push(node);

        return drawable;
    };

    /**
     * Promote the current edit to a real absqcomment.
     *
     * @public
     * @method init_from_edit
     * @param M.assignfeedback_editpdf.edit edit
     * @return bool true if absqcomment bound is more than min width/height, else false.
     */
    this.init_from_edit = function(edit) {
        // eslint-disable-next-line no-console
        console.log('INIT FRM EDIT');
        // eslint-disable-next-line no-console
        console.log(edit);
        var bounds = new M.assignfeedback_editpdf.rect();
        bounds.bound([edit.start, edit.end]);

        if (bounds.width < 40) {
            bounds.width = 40;
        }
        if (bounds.height < 40) {
            bounds.height = 40;
        }
        this.gradeid = this.editor.get('gradeid');
        this.pageno = this.editor.currentpage;
        this.x = bounds.x;
        this.y = bounds.y;
        this.endx = bounds.x + bounds.width;
        this.endy = bounds.y + bounds.height;
        this.rawtext = '';
        this.points = 0;
        this.sequence = 0;
        this.questionid = 0;
        this.commentid = 0;

        // Min width and height is always more than 40px.
        return true;
    };

    /**
     * Update absqcomment position when rotating page.
     * @public
     * @method updatePosition
     */
    this.updatePosition = function() {
        var node = this.drawable.nodes[0].one('div');
        var container = node.ancestor('div');

        var newlocation = new M.assignfeedback_editpdf.point(this.x, this.y);
        var windowlocation = this.editor.get_window_coordinates(newlocation);

        container.setX(windowlocation.x);
        container.setY(windowlocation.y);
        this.drawable.store_position(container, windowlocation.x, windowlocation.y);
    };

    /**
     * Edit exist absq comment
     * @public
     * @method edit_absqcomment
     * @param e
     */
    this.edit_absqcomment = function(e) {
        var absqeditor,
            origtext,
            node;
        e.preventDefault();
        this.menu.hide();
        node = this.drawable.nodes[0].one('div');
        absqeditor = Y.one('#absq_editoreditable');
        origtext = this.rawtext;
        absqeditor.set('innerHTML', this.rawtext);
        if (!this.editor.absqeditorwindow) {
            this.editor.absqeditorwindow = new M.assignfeedback_editpdf.absqeditor({
                editor: this
            });
        }
        var absqeditorwindow = this.editor.absqeditorwindow;
        // custom change
        // var cancelbuton = absqeditorwindow.getButton('cancel', Y.WidgetStdMod.FOOTER);
        // cancelbuton.on('click', function (e) {
        //     e.preventDefault();
        //     this.rawtext = origtext;
        //     absqeditorwindow.hide();
        // }, this);
        // var confirmbutton = absqeditorwindow.getButton('confirm', Y.WidgetStdMod.FOOTER);
        // confirmbutton.on('click', function (){
        //     // Save the changes back to the comment.
        //     var textarea = Y.one('#absq_editor');
        //     this.rawtext = textarea.get('value');
        //     this.draw(node);
        //     absqeditorwindow.hide();
        // }, this);
        absqeditorwindow.show();
    };

};

M.assignfeedback_editpdf = M.assignfeedback_editpdf || {};
M.assignfeedback_editpdf.absqcomment = ABSQCOMMENT;
