YUI.add('moodle-atto_a11ycolors-button', function (Y, NAME) {

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
 * @package    atto_a11ycolors
 * @copyright  2013 Damyon Wiese  <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_a11ycolors-button
 */

/**
 * Atto text editor link plugin.
 *
 * @namespace M.atto_a11ycolors
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_a11ycolors',
    SELECTORS = {},
    STRINGS = {},
    TEMPLATE = null;
Y.namespace('M.atto_a11ycolors').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

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
    _colorDefault: null,
    _colorBgDefault: null,
    _pageDirection: document.getElementsByTagName("html")[0].getAttribute("dir"),
    _maxRowIndex: null,
    _maxColIndex: null,

    initializer: function(config) {
        var self = this;

        // Get translations of strings and create template for popup.
        require(['core/str', 'core/notification'], function(str, notification) {
            var translation = str.get_strings([
                {key: 'sample_text_based_on', component: 'atto_a11ycolors'},
                {key: 'font_color', component: 'atto_a11ycolors'},
                {key: 'background_color', component: 'atto_a11ycolors'},
                {key: 'ok', component: 'atto_a11ycolors'},
                {key: 'restore', component: 'atto_a11ycolors'},
                {key: 'selectdefaulttext', component: 'atto_a11ycolors'},
                {key: 'previewofyourselection', component: 'atto_a11ycolors'},
                {key: 'chooseafontcolor', component: 'atto_a11ycolors'},
                {key: 'chooseabackgroundcolor', component: 'atto_a11ycolors'},
                {key: 'selectionorder', component: 'atto_a11ycolors'},
                {key: 'modaltitle', component: COMPONENTNAME}
            ]);
            $.when(translation).done(function(strings) {

                self._colorDefault = config.colors;
                self._colorBgDefault = config.colorsbackground;
                self._maxRowIndex = self._colorDefault.length;
                self._maxColIndex = self._colorDefault[0].length;

                var fontColors = self._createColorBlocks(self._colorDefault);
                var bgColors = self._createColorBlocks(self._colorBgDefault);
                STRINGS.TEMPLATETEXT = strings[0];
                STRINGS.FONTCOLORTITLE = strings[1];
                STRINGS.BGCOLORTITLE = strings[2];
                STRINGS.OKBTNTITLE = strings[3];
                STRINGS.RESTOREBTNTITLE = strings[4];
                STRINGS.SELECTDEFAULTTEXT = strings[5];
                STRINGS.OVERTEMPLATETEXT = strings[6];
                STRINGS.FONTCOLORSTEXT = strings[7];
                STRINGS.BGCOLORSTEXT = strings[8];
                STRINGS.SELECTLABELTEXT = strings[9];
                STRINGS.MODALTITLE = strings[10];

                TEMPLATE =
                    '<div class="font-bg-colors">' +
                    '<div class="d-flex justify-content-start align-items-center">' +
                    '<label class="mb-0 mr-1" for="selectColor">' + STRINGS.SELECTLABELTEXT + '  </label>' +
                    '<select id = "selectColor" class="custom-select custom-select-sm">' +
                    '<option value="font-colors">' + STRINGS.FONTCOLORSTEXT + '</option>' +
                    '<option value="bg-colors">' + STRINGS.BGCOLORSTEXT + '</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="d-flex justify-content-around mb-1 color-blocks">' +
                    '<div class="font-colors wrapper" data-type="color" style="margin-right: 3px;" data-state="master">' +
                    '<div class="radiobtn-wrapper d-flex align-items-center justify-content-center" id="setFontColor">' +
                    '<span class="custom-radio active">' +
                    '<span class="custom-radio-inner"></span>' +
                    '</span>' +
                    '<h5 class="colors-title bold text-primary mb-0">' + STRINGS.FONTCOLORTITLE + '</h5>' +
                    '</div>' +
                    '<div class="colors-inner" tabindex="0">' + fontColors + '</div>' +
                    '</div>' +
                    '<div class="bg-colors wrapper" data-type="bg" style="margin-left:3px;margin-right: 12px;" data-state="">' +
                    '<div class="radiobtn-wrapper d-flex align-items-center justify-content-center" id="setBgColor">' +
                    '<span class="custom-radio">' +
                    '<span class="custom-radio-inner"></span>' +
                    '</span>' +
                    '<h5 class="colors-title bold text-primary mb-0">' + STRINGS.BGCOLORTITLE + '</h5>' +
                    '</div>' +
                    '<div class="colors-inner" tabindex="0">' + bgColors + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<h5 class="bold text-primary text-left mb-1 w-100">' + STRINGS.OVERTEMPLATETEXT + '</h5>' +
                    '<div class="text mb-2 py-1" data-text="' + STRINGS.TEMPLATETEXT + '">' + STRINGS.TEMPLATETEXT + '</div>' +
                    '<div class="d-flex align-items-center justify-content-between">' +
                    '<button class="btn btn-outline-primary" title="' + STRINGS.RESTOREBTNTITLE + '" id="restoreColorDefaults">' +
                    '<i class="fa fa-undo" aria-hidden="true"></i> ' + STRINGS.RESTOREBTNTITLE +
                    '</button>' +
                    '<button class="btn btn-outline-secondary" title="' + STRINGS.OKBTNTITLE + '" id="changeSelectedText">' +
                    '<i class="fa fa-check" aria-hidden="true"></i> ' + STRINGS.OKBTNTITLE +
                    '</button>' +
                    '</div>' +
                    '</div>';
           }).fail(notification.exception);
       });

        // Add the link button first.
        this.addButton({
            icon: 'e/text_highlight_picker',
            callback: this._displayDialogue,
            tagMatchRequiresAll: false
       });
   },

    config: {},

    /**
     * Init selectors.
     *
     * @method _initSelectors
     * @param  {node}
     * @private
     */

    _initSelectors: function(el) {
        SELECTORS.SELECT = el.querySelector('#selectColor');
        SELECTORS.CONFIRMBTN = el.querySelector('#changeSelectedText');
        SELECTORS.RESTOREBTN = el.querySelector('#restoreColorDefaults');
        SELECTORS.PARENT = el.querySelector('.font-bg-colors');
        SELECTORS.COLORBLOCKSWRAPPERS = el.querySelectorAll('.wrapper');
        SELECTORS.COLORSINNER = el.querySelectorAll('.colors-inner');
        SELECTORS.TEXTPREVIEWBLOCK = el.querySelector('.text');
        SELECTORS.WARNINGTEXT = el.querySelector('.warning-text');
   },

    /**
     * Display the link editor.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function() {
        var self = this;

        var dialogue = this.getDialogue({
            headerContent: STRINGS.MODALTITLE,
            width: 'auto',
            focusAfterHide: true,
            FocusOnShowSelector: SELECTORS.SELECT
       });

        // Save curent selection in variable for use it in future code.

        if (document.getSelection().toString().trim().length > 0) {
            self._currentSelection = this.get('host').getSelection();
            isSelectedText = true;
       } else {
            self._currentSelection = null;
       }

        // Check if text in textarea si selected, if not - show the alert text inside modal.
        if (!self._currentSelection) {
            dialogue.bodyNode._node.replaceChildren();
            dialogue.set('headerContent', STRINGS.MODALTITLE);
            dialogue.set('bodyContent', '<h2 class="mb-0">' + STRINGS.SELECTDEFAULTTEXT + '</h2>');
            dialogue.bodyNode._node.classList.add('atto_a11ycolors-modal');
            dialogue.show();
       } else {
            dialogue.set('headerContent', STRINGS.MODALTITLE);
            dialogue.set('bodyContent', this._getDialogueContent());
            dialogue.bodyNode._node.classList.add('atto_a11ycolors-modal');
            dialogue.show();
            // Init all selectors, when modal is shown.
            this._initSelectors(dialogue.bodyNode._node);

            dialogue.on('render', function() {
                /*    Dialogue.set('bodyContent', self._getDialogueContent()); */
                SELECTORS.TEXTPREVIEWBLOCK.innerHTML = SELECTORS.TEXTPREVIEWBLOCK.dataset.text;

           });

            // Set "master" colorblock when selct value is changed.
            SELECTORS.SELECT.addEventListener('change', function(e) {
                var value = e.target.value;

                SELECTORS.COLORBLOCKSWRAPPERS.forEach(function(el) {
                    el.dataset.state = '';
               });

                SELECTORS.PARENT.querySelector('.' + value).dataset.state = 'master';

                SELECTORS.COLORBLOCKSWRAPPERS.forEach(function(el) {
                    if (el.dataset.state === "master") {

                        var targetEl = el.querySelector('.colors-inner');

                        if (el.dataset.type === 'color') {
                            self._generateColorsDefault(targetEl);
                       }

                        if (el.dataset.type === 'bg') {
                            self._generateBgColorsDefault(targetEl);
                       }
                   }
               });
           });

            // Change style for clicking on color btn.
            SELECTORS.COLORSINNER.forEach(function(el) {
                el.addEventListener('click', function(e) {
                    self._changeStyle(e);
               });
           });

            // Confirm style changes and close modal.
            SELECTORS.CONFIRMBTN.addEventListener('click', function() {
                self._getColorData(self);
                SELECTORS.TEXTPREVIEWBLOCK.setAttribute('style', '');
                self.getDialogue().set('focusAfterHide', null).hide();
                this._currentSelection = null;
           });

            // Restore default text in preview text block.
            SELECTORS.RESTOREBTN.addEventListener('click', function() {
                SELECTORS.TEXTPREVIEWBLOCK.setAttribute('style', '');
                self._generateBgColorsDefault(document.querySelector('.font-colors .colors-inner'));
                self._generateColorsDefault(document.querySelector('.bg-colors .colors-inner'));
                /* SELECTORS.TEXTPREVIEWBLOCK.innerHTML = SELECTORS.TEXTPREVIEWBLOCK.dataset.text; */
           });

            SELECTORS.COLORSINNER.forEach(function(el) {
                /**
                 * Set focus at first color block element.
                 * @param  {*} target
                 */
                function setFocusOnFirstColor(target) {
                    target.blur();
                    var colorblock = target.querySelector('.color-item');
                        colorblock.setAttribute('tabindex', '0');
                        colorblock.focus();
               }

                el.addEventListener('keydown', function(e) {
                    switch (e.key) {
                        case "ArrowRight": {
                            setFocusOnFirstColor(e.target);
                            break;
                       }
                        case "ArrowLeft": {
                            setFocusOnFirstColor(e.target);
                            break;
                       }
                        case "ArrowDown": {
                            setFocusOnFirstColor(e.target);
                            break;
                       }
                        case "ArrowUp": {
                            setFocusOnFirstColor(e.target);
                            break;
                       }
                   }
               });
           });

            SELECTORS.PARENT.querySelectorAll('.color-item').forEach(function(el) {

                /** Set focus to new colorblock.
                 * @param  {*} parent - current focused element.
                 * @param  {number} newrow - current row with element
                 * @param  {number} newcol - current col with element
                 * @returns {boolean}.
                 */
                function moveto(parent, newrow, newcol) {
                    var newTarget = parent.querySelector('[data-rowindex="' + newrow + '"][data-colindex="' + newcol + '"]');
                    if (newTarget.getAttribute('role') === 'button') {
                        parent.querySelectorAll('[role=button]').forEach(function(el) {
                            el.setAttribute('tabindex', '-1');
                       });
                        newTarget.setAttribute('tabindex', '0');
                        newTarget.focus();
                        return true;
                   } else {
                        return false;
                   }
               }

                // eslint-disable-next-line complexity
                el.addEventListener('keydown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var parent = e.target.closest('.colors-inner');
                    var row = parseInt(e.target.dataset.rowindex, 10);
                    var col = parseInt(e.target.dataset.colindex, 10);
                    var newrow;
                    var newcol;
                    switch (e.key) {
                        case "ArrowRight": {
                            if (self._pageDirection === "rtl") {
                                newrow = col === 1 ? row - 1 : row;
                                newcol = col === 1 ? self._maxColIndex : col - 1;
                                newrow = newrow < 1 ? self._maxRowIndex : newrow;
                            } else {
                                newrow = col === self._maxColIndex ? row + 1 : row;
                                newcol = col === self._maxColIndex ? 1 : col + 1;
                                newrow = newrow > self._maxRowIndex ? 1 : newrow;
                            }
                            moveto(parent, newrow, newcol);
                            break;
                       }
                        case "ArrowLeft": {
                            if (self._pageDirection === "rtl") {
                                newrow = col === self._maxColIndex ? row + 1 : row;
                                newcol = col === self._maxColIndex ? 1 : col + 1;
                                newrow = newrow > self._maxRowIndex ? 1 : newrow;
                            } else {
                                newrow = col === 1 ? row - 1 : row;
                                newcol = col === 1 ? self._maxColIndex : col - 1;
                                newrow = newrow < 1 ? self._maxRowIndex : newrow;
                            }
                            moveto(parent, newrow, newcol);
                            break;
                       }
                        case "ArrowDown":
                            newrow = row === self._maxRowIndex ? 1 : row + 1;
                            newrow = newrow < 1 ? self._maxRowIndex : newrow;
                            moveto(parent, newrow, col);
                            break;
                        case "ArrowUp":
                            newrow = row === 1 ? self._maxRowIndex : row - 1;
                            newrow = newrow === 0 ? 1 : newrow;
                            moveto(parent, newrow, col);
                            break;
                        case "Home": {
                            moveto(parent, 1, 1);
                            break;
                       }
                        case "End": {
                            moveto(parent, self._maxRowIndex, self._maxColIndex);
                            break;
                       }
                        case "Tab": {
                            parent.querySelectorAll('[role=button]').forEach(function(el) {
                                el.setAttribute('tabindex', '-1');
                           });
                            parent.focus();
                            break;
                       }
                        case "Enter": {
                            e.target.click();
                            break;
                       }
                   }
               });
           });

       }
   },

    /**
     * Generates the content of the dialogue.
     *
     * @method _getDialogueContent
     * @return {Node} Node containing the dialogue content
     * @private
     */
    _getDialogueContent: function() {
        var template = Y.Handlebars.compile(TEMPLATE);

        this._content = Y.Node.create(template({
            component: COMPONENTNAME,
            CSS: CSS
       }));

        return this._content;
   },
    _createColorBlocks: function(colorsArr) {
        var colorBlocks = '';
        colorsArr.forEach(function(tempArr, i) {
            var rowIndex = i + 1;
            tempArr.forEach(function(el, i) {
                var colIndex = i + 1;
                colorBlocks += '<div class="color-item" role="button" style="background-color:' + el.trim() + '"' +
                    ' data-color="' + el.trim() + '" data-clickablestate="true" data-rowindex = "' + rowIndex + '"' +
                    ' data-colindex = "' + colIndex + '"></div>';
           });
       });
        return colorBlocks;
   },
    _getBestColor: function(currentcolor, basecolor) {

        var ratio = M.tinycolor.readability(basecolor, currentcolor);
        var result = {};
        if (ratio < 4.5) {
            result.color = '#fff';
            result.clickable = false;
       } else {
            result.color = basecolor;
            result.clickable = true;
       }
        return result;
   },
    _generateColors: function(targetEl, color) {
        var self = this;
        this._generateColorsDefault(targetEl);
        var insideColors = targetEl.querySelectorAll('.color-item');
        insideColors.forEach(function(el) {

            var fontColor = color.toLowerCase();
            var bgColor = el.dataset.color.toLowerCase();
            var randomColor = self._getBestColor(fontColor, bgColor);

            el.dataset.color = randomColor.color;
            el.style.backgroundColor = randomColor.color;
            el.dataset.clickablestate = randomColor.clickable;
       });
   },
    _generateColorsDefault: function(targetEl) {
        var insideColors = targetEl.querySelectorAll('.color-item');

        var colorArr = [];
        this._colorDefault.forEach(function(tempArr) {
            tempArr.forEach(function(el) {
                colorArr.push(el.trim());
           });
       });

        insideColors.forEach(function(el, i) {
            var defaultColor = 'c0b782';
            if (colorArr[i] !== undefined) {
                defaultColor = colorArr[i];
           }

            el.dataset.color = defaultColor;
            el.style.backgroundColor = defaultColor;
       });
   },
    _generateBgColors: function(targetEl, color) {
        var self = this;


        this._generateBgColorsDefault(targetEl);

        var insideColors = targetEl.querySelectorAll('.color-item');

        insideColors.forEach(function(el) {

            var bgColor = color.toLowerCase();
            var fontColor = el.dataset.color.toLowerCase();
            var randomColor = self._getBestColor(bgColor, fontColor);

            el.dataset.color = randomColor.color;
            el.style.backgroundColor = randomColor.color;
            el.dataset.clickablestate = randomColor.clickable;
       });
   },
    _generateBgColorsDefault: function(targetEl) {
        var insideColors = targetEl.querySelectorAll('.color-item');
        var colorArr = [];

        this._colorBgDefault.forEach(function(tempArr, rowIndex) {
            tempArr.forEach(function(el, index) {
                colorArr.push(el.trim());
           });
       });

        insideColors.forEach(function(el, i) {
            var defaultColor = 'c0b782';
            if (colorArr[i] !== undefined) {
                defaultColor = colorArr[i];
           }

            el.dataset.color = defaultColor;
            el.style.backgroundColor = defaultColor;
            el.dataset.clickablestate = true;
       });
   },
    _changeStyle: function(e, color) {

        e.preventDefault();
        e.stopPropagation();

        var target = e.target;


        var text = SELECTORS.PARENT.getElementsByClassName('text');
        var clickablestate = target.getAttribute("data-clickablestate");

        color = target.getAttribute("data-color");

        if (color && clickablestate === "true") {
            var colorItem = target;
            var activeColor;
            var colorItemParent = colorItem.closest('.wrapper');
            var colorsParent = document.querySelector(".font-colors .colors-inner");
            var bgcolorsParent = document.querySelector(".bg-colors .colors-inner");
            var propertyType = colorItemParent.getAttribute("data-type");
            if (propertyType === 'color') {
                activeColor = colorsParent.getElementsByClassName('active-color');

                if (activeColor.length > 0) {
                    activeColor[0].classList.remove('active-color');
               }

                colorItem.classList.add('active-color');
                text[0].style.color = color;
                this.config.color = color;
           }
            if (propertyType === 'bg') {
                activeColor = bgcolorsParent.getElementsByClassName('active-color');

                if (activeColor.length > 0) {
                    activeColor[0].classList.remove('active-color');
               }
                colorItem.classList.add('active-color');
                text[0].style.backgroundColor = color;
                this.config['background-color'] = color;
           }
            if (target.closest('.wrapper').dataset.state === 'master') {
                var targetEl;
                SELECTORS.COLORBLOCKSWRAPPERS.forEach(function(el) {
                    if (el.dataset.state.length === 0) {
                        targetEl = el.querySelector('.colors-inner');
                   }
               });

                if (propertyType === 'color') {
                    this._generateColors(targetEl, color);
                    activeColor = bgcolorsParent.getElementsByClassName('active-color');
                    if (activeColor.length > 0) {
                        activeColor[0].classList.remove('active-color');
                   }
               }

                if (propertyType === 'bg') {
                    this._generateBgColors(targetEl, color);
                    activeColor = colorsParent.getElementsByClassName('active-color');
                    if (activeColor.length > 0) {
                        activeColor[0].classList.remove('active-color');
                   }
               }
           }
       }

   },
    _getColorData: function() {
        this.get('host').setSelection(this._currentSelection);
        this.get('host').formatSelectionInlineStyle(this.config);

        for (var prop in Object.getOwnPropertyNames(this.config)) {
            delete this.config[prop];
       }

        this.get('host').saveSelection();
        return this.get('host').updateOriginal();
   }

},
    {
        ATTRS: {
            /**
             * The list of available colors
             *
             * @attribute colors
             * @type array
             * @default {}
             */
            colors: {
                value: {}
           }
       }
   }, {
    ATTRS: {
        /**
         * The list of available colors
         *
         * @attribute colors
         * @type array
         * @default {}
         */
        colors: {
            value: {}
       }
   }
});


}, '@VERSION@', {"requires": ["node"]});
