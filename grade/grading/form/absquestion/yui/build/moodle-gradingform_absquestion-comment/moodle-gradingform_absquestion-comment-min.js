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
            },

        });

        M.assignfeedback_editpdf.ABSQUESTIONCOMMENT = ABSQUESTIONCOMMENT;

        M.assignfeedback_editpdf.absquestioncomment = M.assignfeedback_editpdf.absquestioncomment || {};

        M.assignfeedback_editpdf.absquestioncomment.init = M.assignfeedback_editpdf.absquestioncomment.init || function() {
            return M.assignfeedback_editpdf.instance;
        };
        var DRAGSECTION = function() {
            DRAGSECTION.superclass.constructor.apply(this, arguments);
        };
        Y.extend(DRAGSECTION, M.core.dragdrop, {
            sectionlistselector: null,

            initializer: function() {
                // Set group for parent class
                this.groups = [CSS.SECTIONDRAGGABLE];
                this.samenodeclass = M.course.format.get_sectionwrapperclass();
                this.parentnodeclass = M.course.format.get_containerclass();
                // Detect the direction of travel.
                this.detectkeyboarddirection = true;

                // Check if we are in single section mode
                if (Y.Node.one('.' + CSS.JUMPMENU)) {
                    return false;
                }
                // Initialise sections dragging
                this.sectionlistselector = M.course.format.get_section_wrapper(Y);
                if (this.sectionlistselector) {
                    this.sectionlistselector = '.' + CSS.COURSECONTENT + ' ' + this.sectionlistselector;

                    this.setup_for_section(this.sectionlistselector);

                    // Make each li element in the lists of sections draggable
                    var del = new Y.DD.Delegate({
                        container: '.' + CSS.COURSECONTENT,
                        nodes: '.' + CSS.SECTIONDRAGGABLE,
                        target: true,
                        handles: ['.' + CSS.LEFT],
                        dragConfig: {groups: this.groups}
                    });
                    del.dd.plug(Y.Plugin.DDProxy, {
                        // Don't move the node at the end of the drag
                        moveOnEnd: false
                    });
                    del.dd.plug(Y.Plugin.DDConstrained, {
                        // Keep it inside the .course-content
                        constrain: '#' + CSS.PAGECONTENT,
                        stickY: true
                    });
                    del.dd.plug(Y.Plugin.DDWinScroll);
                }
            },

            /**
             * Apply dragdrop features to the specified selector or node that refers to section(s)
             *
             * @method setup_for_section
             * @param {String} baseselector The CSS selector or node to limit scope to
             */
            setup_for_section: function(baseselector) {
                Y.Node.all(baseselector).each(function(sectionnode) {
                    // Determine the section ID
                    var sectionid = Y.Moodle.core_course.util.section.getId(sectionnode);

                    // We skip the top section as it is not draggable
                    if (sectionid > 0) {
                        // Remove move icons
                        var movedown = sectionnode.one('.' + CSS.RIGHT + ' a.' + CSS.MOVEDOWN);
                        var moveup = sectionnode.one('.' + CSS.RIGHT + ' a.' + CSS.MOVEUP);

                        // Add dragger icon
                        var title = M.util.get_string('movesection', 'moodle', sectionid);
                        var cssleft = sectionnode.one('.' + CSS.LEFT);

                        if ((movedown || moveup) && cssleft) {
                            cssleft.setStyle('cursor', 'move');
                            cssleft.appendChild(this.get_drag_handle(title, CSS.SECTIONHANDLE, 'icon', true));

                            if (moveup) {
                                if (moveup.previous('br')) {
                                    moveup.previous('br').remove();
                                } else if (moveup.next('br')) {
                                    moveup.next('br').remove();
                                }

                                if (moveup.ancestor('.section_action_menu') && moveup.ancestor().get('nodeName').toLowerCase() == 'li') {
                                    moveup.ancestor().remove();
                                } else {
                                    moveup.remove();
                                }
                            }
                            if (movedown) {
                                if (movedown.previous('br')) {
                                    movedown.previous('br').remove();
                                } else if (movedown.next('br')) {
                                    movedown.next('br').remove();
                                }

                                var movedownParentType = movedown.ancestor().get('nodeName').toLowerCase();
                                if (movedown.ancestor('.section_action_menu') && movedownParentType == 'li') {
                                    movedown.ancestor().remove();
                                } else {
                                    movedown.remove();
                                }
                            }

                            // This section can be moved - add the class to indicate this to Y.DD.
                            sectionnode.addClass(CSS.SECTIONDRAGGABLE);
                        }
                    }
                }, this);
            },

            /*
             * Drag-dropping related functions
             */
            drag_start: function(e) {
                // Get our drag object
                var drag = e.target;
                // This is the node that the user started to drag.
                var node = drag.get('node');
                // This is the container node that will follow the mouse around,
                // or during a keyboard drag and drop the original node.
                var dragnode = drag.get('dragNode');
                if (node === dragnode) {
                    return;
                }
                // Creat a dummy structure of the outer elemnents for clean styles application
                var containernode = Y.Node.create('<' + M.course.format.get_containernode() +
                    '></' + M.course.format.get_containernode() + '>');
                containernode.addClass(M.course.format.get_containerclass());
                var sectionnode = Y.Node.create('<' + M.course.format.get_sectionwrappernode() +
                    '></' + M.course.format.get_sectionwrappernode() + '>');
                sectionnode.addClass(M.course.format.get_sectionwrapperclass());
                sectionnode.setStyle('margin', 0);
                sectionnode.setContent(node.get('innerHTML'));
                containernode.appendChild(sectionnode);
                dragnode.setContent(containernode);
                dragnode.addClass(CSS.COURSECONTENT);
            },

            drag_dropmiss: function(e) {
                // Missed the target, but we assume the user intended to drop it
                // on the last last ghost node location, e.drag and e.drop should be
                // prepared by global_drag_dropmiss parent so simulate drop_hit(e).
                this.drop_hit(e);
            },

            get_section_index: function(node) {
                var sectionlistselector = '.' + CSS.COURSECONTENT + ' ' + M.course.format.get_section_selector(Y),
                    sectionList = Y.all(sectionlistselector),
                    nodeIndex = sectionList.indexOf(node),
                    zeroIndex = sectionList.indexOf(Y.one('#section-0'));

                return (nodeIndex - zeroIndex);
            },

            drop_hit: function(e) {
                var drag = e.drag;

                // Get references to our nodes and their IDs.
                var dragnode = drag.get('node'),
                    dragnodeid = Y.Moodle.core_course.util.section.getId(dragnode),
                    loopstart = dragnodeid,

                    dropnodeindex = this.get_section_index(dragnode),
                    loopend = dropnodeindex;

                if (dragnodeid === dropnodeindex) {
                    return;
                }


                if (loopstart > loopend) {
                    // If we're going up, we need to swap the loop order
                    // because loops can't go backwards.
                    loopstart = dropnodeindex;
                    loopend = dragnodeid;
                }

                // Get the list of nodes.
                drag.get('dragNode').removeClass(CSS.COURSECONTENT);
                var sectionlist = Y.Node.all(this.sectionlistselector);

                // Add a lightbox if it's not there.
                var lightbox = M.util.add_lightbox(Y, dragnode);

                // Handle any variables which we must pass via AJAX.
                var params = {},
                    pageparams = this.get('config').pageparams,
                    varname;

                for (varname in pageparams) {
                    if (!pageparams.hasOwnProperty(varname)) {
                        continue;
                    }
                    params[varname] = pageparams[varname];
                }

                // Prepare request parameters
                params.sesskey = M.cfg.sesskey;
                params.courseId = this.get('courseid');
                params['class'] = 'section';
                params.field = 'move';
                params.id = dragnodeid;
                params.value = dropnodeindex;

                // Perform the AJAX request.
                var uri = M.cfg.wwwroot + this.get('ajaxurl');
                Y.io(uri, {
                    method: 'POST',
                    data: params,
                    on: {
                        start: function() {
                            lightbox.show();
                        },
                        success: function(tid, response) {
                            // Update section titles, we can't simply swap them as
                            // they might have custom title
                            try {
                                var responsetext = Y.JSON.parse(response.responseText);
                                if (responsetext.error) {
                                    new M.core.ajaxException(responsetext);
                                }
                                M.course.format.process_sections(Y, sectionlist, responsetext, loopstart, loopend);
                            } catch (e) {
                                // Ignore.
                            }

                            // Update all of the section IDs - first unset them, then set them
                            // to avoid duplicates in the DOM.
                            var index;

                            // Classic bubble sort algorithm is applied to the section
                            // nodes between original drag node location and the new one.
                            var swapped = false;
                            do {
                                swapped = false;
                                for (index = loopstart; index <= loopend; index++) {
                                    if (Y.Moodle.core_course.util.section.getId(sectionlist.item(index - 1)) >
                                        Y.Moodle.core_course.util.section.getId(sectionlist.item(index))) {
                                        // Swap section id.
                                        var sectionid = sectionlist.item(index - 1).get('id');
                                        sectionlist.item(index - 1).set('id', sectionlist.item(index).get('id'));
                                        sectionlist.item(index).set('id', sectionid);

                                        // See what format needs to swap.
                                        M.course.format.swap_sections(Y, index - 1, index);

                                        // Update flag.
                                        swapped = true;
                                    }
                                    sectionlist.item(index).setAttribute('data-sectionid',
                                        Y.Moodle.core_course.util.section.getId(sectionlist.item(index)));
                                }
                                loopend = loopend - 1;
                            } while (swapped);

                            window.setTimeout(function() {
                                lightbox.hide();
                            }, 250);
                        },

                        failure: function(tid, response) {
                            this.ajax_failure(response);
                            lightbox.hide();
                        }
                    },
                    context: this
                });
            }

        }, {
            NAME: 'course-dragdrop-section',
            ATTRS: {
                courseid: {
                    value: null
                },
                ajaxurl: {
                    value: 0
                },
                config: {
                    value: 0
                }
            }
        });


}, '@VERSION@', {"requires": ["moodle-assignfeedback_editpdf-editor"]}
);
