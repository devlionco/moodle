var ABSQCOMMENTMENUNAME = "Absqcommentmenu",
    ABSQCOMMENTMENU;

/**
 * Provides an in browser PDF editor.
 *
 * @module moodle-assignfeedback_editpdf-editor
 */

/**
 * ABSQCOMMENTMENU
 * This is a drop down list of comment context functions.
 *
 * @namespace M.assignfeedback_editpdf
 * @class commentmenu
 * @constructor
 * @param {Object} config
 * @extends M.assignfeedback_editpdf.dropdown
 */
ABSQCOMMENTMENU = function(config) {
    ABSQCOMMENTMENU.superclass.constructor.apply(this, [config]);
};

Y.extend(ABSQCOMMENTMENU, M.assignfeedback_editpdf.dropdown, {

    /**
     * Initialise the menu.
     *
     * @method initializer
     * @param {Object} config
     */
    initializer: function(config) {
        var absqcommentlinks,
            link,
            body,
            absqcomment;

        absqcomment = this.get('absqcomment');
        // Build the list of menu items.
        absqcommentlinks = Y.Node.create('<ul role="menu" class="assignfeedback_editpdf_menu"/>');
        // custom change
        // link = Y.Node.create('<li><a tabindex="-1" href="#">' +
        //        M.util.get_string('editabsq', 'assignfeedback_editpdf') +
        //        '</a></li>');
        // link.on('click', absqcomment.edit_absqcomment, absqcomment);
        // link.on('key', absqcomment.edit_absqcomment, 'enter,space', absqcomment);

        // absqcommentlinks.append(link);

        link = Y.Node.create('<li><a tabindex="-1" href="#">' +
               M.util.get_string('deletecomment', 'assignfeedback_editpdf') +
               '</a></li>');
        link.on('click', function(e) {
            e.preventDefault();
            this.menu.hide();
            this.remove();
        }, absqcomment);

        link.on('key', function() {
            absqcomment.menu.hide();
            absqcomment.remove();
        }, 'enter,space', absqcomment);

        absqcommentlinks.append(link);

        link = Y.Node.create('<li><hr/></li>');
        absqcommentlinks.append(link);

        // Set the accessible header text.
        this.set('headerText', M.util.get_string('commentcontextmenu', 'assignfeedback_editpdf'));

        body = Y.Node.create('<div/>');

        // Set the body content.
        body.append(absqcommentlinks);
        this.set('bodyContent', body);

        ABSQCOMMENTMENU.superclass.initializer.call(this, config);
    }
}, {
    NAME: ABSQCOMMENTMENUNAME,
    ATTRS: {
        /**
         * The comment this menu is attached to.
         *
         * @attribute comment
         * @type M.assignfeedback_editpdf.comment
         * @default null
         */
        absqcomment: {
            value: null
        }

    }
});

M.assignfeedback_editpdf = M.assignfeedback_editpdf || {};
M.assignfeedback_editpdf.absqcommentmenu = ABSQCOMMENTMENU;
