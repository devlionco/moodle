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
var SELECTOR = {
        PREVIOUSBUTTON:  '.navigate-previous-button',
        NEXTBUTTON:  ' .navigate-next-button',
        SEARCHCOMMENTSBUTTON: '.searchcommentsbutton',
        EXPCOLCOMMENTSBUTTON: '.expcolcommentsbutton',
        SEARCHFILTER: '.assignfeedback_editpdf_commentsearch input',
        SEARCHCOMMENTSLIST: '.assignfeedback_editpdf_commentsearch ul',
        PAGESELECT: '.navigate-page-select',
        LOADINGICON: '.loading',
        PROGRESSBARCONTAINER: '.progress-info.progress-striped',
        DRAWINGREGION: '.drawingregion',
        DRAWINGCANVAS: '.drawingcanvas',
        SAVE: '.savebutton',
        COMMENTCOLOURBUTTON: '.commentcolourbutton',
        COMMENTMENU: '.commentdrawable a',
        ANNOTATIONCOLOURBUTTON:  '.annotationcolourbutton',
        DELETEANNOTATIONBUTTON: '.deleteannotationbutton',
        WARNINGMESSAGECONTAINER: '.warningmessages',
        ICONMESSAGECONTAINER: '.infoicon',
        UNSAVEDCHANGESDIV: '.assignfeedback_editpdf_warningmessages',
        UNSAVEDCHANGESINPUT: 'input[name="assignfeedback_editpdf_haschanges"]',
        STAMPSBUTTON: '.currentstampbutton',
        USERINFOREGION: '[data-region="user-info"]',
        ROTATELEFTBUTTON: '.rotateleftbutton',
        ROTATERIGHTBUTTON: '.rotaterightbutton',
        DIALOGUE: '.assignfeedback_editpdf_widget'
    };

YUI.add('moodle-gradingform_absquestion-comment', function (Y, NAME) {
        /**
         * Absquestion
         *
         * @module moodle-gradingform_absquestion-comment
         */

        /**
         * Class representing a stamp.
         *
         * @namespace M.assignfeedback_editpdf
         *
         * edback_editpdf
         * @class ABSQUESTIONCOMMENT
         * @extends M.assignfeedback_editpdf.annotation
         */
        var ABSQUESTIONCOMMENT = function (config) {
            ABSQUESTIONCOMMENT.superclass.constructor.apply(this, [config]);
        };

        ABSQUESTIONCOMMENT.NAME = "ABSQUESTIONCOMMENT";
        ABSQUESTIONCOMMENT.ATTRS = {};

        Y.extend(ABSQUESTIONCOMMENT, M.assignfeedback_editpdf.annotation, {
            /**
             * Draw a stamp annotation
             * @protected
             * @method draw
             * @return M.assignfeedback_editpdf.drawable
             */
            draw: function () {
                var drawable = new M.assignfeedback_editpdf.drawable(this.editor),
                    drawingcanvas = this.editor.get_dialogue_element(SELECTOR.DRAWINGCANVAS),
                    node,
                    position;

                position = this.editor.get_window_coordinates(new M.assignfeedback_editpdf.point(this.x, this.y));
                node = Y.Node.create('<div/>');
                node.addClass('annotation');
                node.addClass('stamp');
                node.setStyles({
                    'position': 'absolute',
                    'display': 'inline-block',
                    'backgroundImage': 'url(' + this.editor.get_stamp_image_url(this.path) + ')',
                    'width': (this.endx - this.x),
                    'height': (this.endy - this.y),
                    'backgroundSize': '100% 100%'
                });

                drawingcanvas.append(node);
                node.setX(position.x);
                node.setY(position.y);
                drawable.store_position(node, position.x, position.y);

                // Bind events only when editing.
                if (!this.editor.get('readonly')) {
                    // Pass through the event handlers on the div.
                    node.on('gesturemovestart', this.editor.edit_start, null, this.editor);
                    node.on('gesturemove', this.editor.edit_move, null, this.editor);
                    node.on('gesturemoveend', this.editor.edit_end, null, this.editor);
                }

                drawable.nodes.push(node);

                this.drawable = drawable;
                return ABSQUESTIONCOMMENT.superclass.draw.apply(this);
            },

            /**
             * Draw the in progress edit.
             *
             * @public
             * @method draw_current_edit
             * @param M.assignfeedback_editpdf.edit edit
             */
            draw_current_edit: function (edit) {
                var bounds = new M.assignfeedback_editpdf.rect(),
                    drawable = new M.assignfeedback_editpdf.drawable(this.editor),
                    drawingregion = this.editor.get_dialogue_element(SELECTOR.DRAWINGREGION),
                    node,
                    position;

                bounds.bound([edit.start, edit.end]);
                position = this.editor.get_window_coordinates(new M.assignfeedback_editpdf.point(bounds.x, bounds.y));

                node = Y.Node.create('<div/>');
                node.addClass('annotation');
                node.addClass('stamp');
                node.setStyles({
                    'position': 'absolute',
                    'display': 'inline-block',
                    'backgroundImage': 'url(' + this.editor.get_stamp_image_url(edit.stamp) + ')',
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
            },

            /**
             * Promote the current edit to a real annotation.
             *
             * @public
             * @method init_from_edit
             * @param M.assignfeedback_editpdf.edit edit
             * @return bool if width/height is more than min. required.
             */
            init_from_edit: function (edit) {
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
                this.colour = edit.annotationcolour;
                this.path = edit.stamp;

                // Min width and height is always more than 40px.
                return true;
            },

            /**
             * Move an annotation to a new location.
             * @public
             * @param int newx
             * @param int newy
             * @method move_annotation
             */
            move: function (newx, newy) {
                var diffx = newx - this.x,
                    diffy = newy - this.y;

                this.x += diffx;
                this.y += diffy;
                this.endx += diffx;
                this.endy += diffy;

                if (this.drawable) {
                    this.drawable.erase();
                }
                this.editor.drawables.push(this.draw());
            }
        });

        M.assignfeedback_editpdf.absquestioncomment = ABSQUESTIONCOMMENT;

        M.assignfeedback_editpdf.absquestioncomment.init = M.assignfeedback_editpdf.absquestioncomment.init || function() {
            return M.assignfeedback_editpdf.instance;
        };

        function drawcomment() {
            var data = {
                type: this.NAME,
                editor: M.assignfeedback_editpdf.editor
            };

            console.log(M.assignfeedback_editpdf.editor);

            var commentinstance = new M.assignfeedback_editpdf.absquestioncomment(data);

            data = {
                type: 'annotationstamp',
                editor: M.assignfeedback_editpdf.editor
            };
            var stampinstance = new M.assignfeedback_editpdf.annotationstamp(data);
            stampinstance.draw();
        }

        //Y.on("click", drawcomment(), ".comment");

    }, '@VERSION@', {
        "requires": [
            "moodle-assignfeedback_editpdf-editor",
            "base",
            "node",
            "io",
            "dom",
            "dd",
            "dd-scroll",
            "moodle-core-dragdrop",
            "moodle-core-notification",
            "moodle-course-coursebase",
            "moodle-course-util"
        ]
    }
);
