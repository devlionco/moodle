YUI.add('moodle-atto_accessibilitycolors-button', function (Y, NAME) {

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
     * Atto text editor integration version file.
     *
     * @package    atto_accessibilitycolors
     * @copyright  2014-2015 Université de Lausanne
     * @author     Nicolas Dunand <nicolas.dunand@unil.ch>
     * @author     Rossiani Wijaya  <rwijaya@moodle.com>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

    /**
     * @module moodle-atto_accessibilitycolors-button
     */

    Y.namespace('M.atto_accessibilitycolors').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
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
         * A reference to the HTMl of the dialuge content
         *
         * @property _content
         * @type String
         * @private
         */
        _content: null,

        _colorDefault: null,
        _colorBgDefault: null,

        initializer: function (config) {

            const self = this;

            require(['core/str', 'core/notification'], function (str, notification) {

                var translation = str.get_strings([
                    { key: 'sample_text_based_on', component: 'atto_accessibilitycolors' },
                    { key: 'font_color', component: 'atto_accessibilitycolors' },
                    { key: 'background_color', component: 'atto_accessibilitycolors' },
                    { key: 'ok', component: 'atto_accessibilitycolors' },
                    { key: 'restore', component: 'atto_accessibilitycolors' },
                    { key: 'selectdefaulttext', component: 'atto_accessibilitycolors' },
                    { key: 'previewofyourselection', component: 'atto_accessibilitycolors' },
                ]);

                $.when(translation).done(function (strings) {

                    self._colorDefault = config.colors;
                    self._colorBgDefault = config.colorsbackground;

                    let fontColors = self._createColorBlocks(self._colorDefault);
                    let bgColors = self._createColorBlocks(self._colorBgDefault);
                    let templateText = strings[0];
                    let fontColorTitle = strings[1];
                    let bgColorTitle = strings[2];
                    let okBtnTitle = strings[3];
                    let restoreBtnTitle = strings[4];
                    let selectDefaultText = strings[5];
                    let overTemplateText = strings[6];


                    let colorsTemplate = `
                                    <div class="dropdown-inner font-bg-colors">
                                        <div class="default-text text-danger small">${selectDefaultText}</div>
                                        <div class="d-flex mb-1">
                                            <div class="font-colors wrapper" data-type="color" style="margin-right: 3px;" data-state="master">            
                                                <div class="radiobtn-wrapper d-flex align-items-center justify-content-center" id="setFontColor">
                                                    <span class="custom-radio active">
                                                        <span class="custom-radio-inner"></span>
                                                    </span>
                                                    <h5 class="colors-title bold text-primary mb-0"> ${fontColorTitle}</h5>
                                                </div>
                                                <div class="colors-inner">
                                                    ${fontColors}
                                                </div>
                                            </div>
                                            <div class="bgc-colors wrapper " data-type="bg" style="margin-left: 3px;margin-right: 12px;" data-state="">  
                                                <div class="radiobtn-wrapper d-flex align-items-center justify-content-center" id="setBgColor">
                                                    <span class="custom-radio">
                                                        <span class="custom-radio-inner"></span>
                                                    </span>
                                                    <h5 class="colors-title bold text-primary mb-0"> ${bgColorTitle}</h5>
                                                </div>            
                                                <div class="colors-inner">
                                                    ${bgColors}
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="bold text-primary text-left mb-1 w-100"> ${overTemplateText}</h5>
                                        <div class="text mb-2 py-1" data-text="${templateText}">${templateText}</div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <button class="btn btn-outline-primary" title="${restoreBtnTitle}" id="restoreColorDefaults"><i class="fa fa-undo" aria-hidden="true"></i> ${restoreBtnTitle}</button>
                                            <button class="btn btn-outline-secondary" title="${okBtnTitle}" id="changeSelectedText"><i class="fa fa-check" aria-hidden="true"></i> ${okBtnTitle}</button>
                                        </div>
                                    </div>
                                    `;

                    self.addToolbarMenu({
                        icon: 'e/text_highlight',
                        overlayWidth: '8',
                        menuColor: '#333333',
                        globalItemConfig: {
                            inlineFormat: true,
                            callback: self._changeStyle
                        },
                        items: [{
                            text: colorsTemplate,
                            callback: self._changeStyle
                        }]
                    });

                    document.addEventListener('click', function (e) {
                        let clickedInsideMenu = e.target.closest(".moodle-dialogue-base") || false;
                        if (e.target.classList.contains === 'atto_accessibilitycolors_button' || e.target.closest(".atto_accessibilitycolors_button")) {
                            const textPreviewBlock = document.querySelector('.text');
                            let selectedText = window.getSelection().toString();
                            if (selectedText.length > 0) {
                                document.getElementsByClassName('default-text')[0].style.visibility = 'hidden';
                                textPreviewBlock.innerHTML = selectedText;
                            } else {
                                document.getElementsByClassName('default-text')[0].style.visibility = 'visible';
                                textPreviewBlock.innerHTML = textPreviewBlock.dataset.text;
                            }

                        }

                        if (e.target.id === 'changeSelectedText' || !clickedInsideMenu) {
                            let element = document.querySelector('.open.atto_accessibilitycolors_button.atto_menu');
                            if (element) {
                                let moodleDialogue = element.closest(".moodle-dialogue");
                                moodleDialogue.classList.remove('has-index');
                                moodleDialogue.classList.remove('d-block');
                            }
                        }

                        if (e.target.classList.contains('radiobtn-wrapper') || e.target.classList.contains('custom-radio') || e.target.classList.contains('colors-title')) {

                            e.preventDefault();
                            e.stopPropagation();

                            let parent = e.target.closest(".dropdown-inner");
                            let allRadioBtns = parent.querySelectorAll('.custom-radio');
                            allRadioBtns.forEach(el => el.classList.remove('active'));
                            let currentBtn = e.target.closest(".wrapper").getElementsByClassName('custom-radio')[0];
                            currentBtn.classList.add('active');
                            parent.querySelectorAll('.wrapper').forEach(el => el.dataset.state = '');

                            e.target.closest('.wrapper').dataset.state = 'master';

                        }

                    });
                }).fail(notification.exception);
            });
        },

        config: {},
        _createColorBlocks: function (colorsArr) {
            let colorBlocks = '';
            colorsArr.forEach(tempArr => {
                tempArr.forEach((el, i) => {
                    colorBlocks += `<div class="color-item" style="background-color: ${el.trim()}"
                    data-color="${el.trim()}" data-clickablestate="true"></div>`;
                });
            });
            return colorBlocks;
        },

        _getBestColor: function (currentcolor, basecolor) {


            const ratio = M.tinycolor.readability(basecolor, currentcolor);
            let result = {};
            if (ratio < 4.5) {
                result.color = '#fff';
                result.clickable = false;
            } else {
                result.color = basecolor;
                result.clickable = true;
            }
            return result;
        },
        _getBestColorOld: function (currentcolor, basecolor) {

            function hexToHSL(H) {

                // Convert hex to RGB first
                let r = 0, g = 0, b = 0;
                if (H.length === 4) {
                    r = "0x" + H[1] + H[1];
                    g = "0x" + H[2] + H[2];
                    b = "0x" + H[3] + H[3];
                } else if (H.length === 7) {
                    r = "0x" + H[1] + H[2];
                    g = "0x" + H[3] + H[4];
                    b = "0x" + H[5] + H[6];
                }

                // Then to HSL
                r /= 255;
                g /= 255;
                b /= 255;
                let cmin = Math.min(r, g, b),
                    cmax = Math.max(r, g, b),
                    delta = cmax - cmin,
                    h = 0,
                    s = 0,
                    l = 0;

                if (delta === 0) {
                    h = 0;
                } else if (cmax === r) {
                    h = ((g - b) / delta) % 6;
                } else if (cmax === g) {
                    h = (b - r) / delta + 2;
                } else {
                    h = (r - g) / delta + 4;
                }

                h = Math.round(h * 60);

                if (h < 0) {
                    h += 360;
                }

                l = (cmax + cmin) / 2;
                s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));
                s = +(s * 100).toFixed(1);
                l = +(l * 100).toFixed(1);
                let obj = {
                    h: h,
                    s: s,
                    l: l
                };

                return obj;
            }

            function HSLToHex(h, s, l) {
                s /= 100;
                l /= 100;

                let c = (1 - Math.abs(2 * l - 1)) * s,
                    x = c * (1 - Math.abs((h / 60) % 2 - 1)),
                    m = l - c / 2,
                    r = 0,
                    g = 0,
                    b = 0;

                if (0 <= h && h < 60) {
                    r = c; g = x; b = 0;
                } else if (60 <= h && h < 120) {
                    r = x; g = c; b = 0;
                } else if (120 <= h && h < 180) {
                    r = 0; g = c; b = x;
                } else if (180 <= h && h < 240) {
                    r = 0; g = x; b = c;
                } else if (240 <= h && h < 300) {
                    r = x; g = 0; b = c;
                } else if (300 <= h && h < 360) {
                    r = c; g = 0; b = x;
                }
                // Having obtained RGB, convert channels to hex
                r = Math.round((r + m) * 255).toString(16);
                g = Math.round((g + m) * 255).toString(16);
                b = Math.round((b + m) * 255).toString(16);

                // Prepend 0s, if necessary
                if (r.length === 1) { r = "0" + r; }
                if (g.length === 1) { g = "0" + g; }
                if (b.length === 1) { b = "0" + b; }

                return "#" + r + g + b;
            }

            function changeHSLlightness(obj) {
                let lightness = +obj.l;
                if (lightness - 10 >= 0) {
                    lightness -= 10;
                } else {
                    lightness = 100;
                }

                obj.l = lightness;

                return HSLToHex(obj.h, obj.s, obj.l);
            }

            var startRatio = +M.tinycolor.readability(currentcolor, basecolor);
            var bestRatio = startRatio;
            var ratio = startRatio;

            var result = {
                color: basecolor,
                clickable: true
            };
            var bestColor = basecolor;

            for (let index = 0; index < 11; index++) {
                if (ratio < 4.5) {

                    let HSLbasecolor = hexToHSL(result);
                    let сhangedHEX = null;

                    сhangedHEX = changeHSLlightness(HSLbasecolor);
                    ratio = M.tinycolor.readability(basecolor, сhangedHEX);

                    if (ratio > bestRatio) {
                        bestColor = сhangedHEX;
                    }
                    result.color = bestColor;
                }
            }

            return result;
        },
        _generateColors: function (targetEl, color) {
            var self = this;
            this._generateColorsDefault(targetEl);

            const insideColors = targetEl.querySelectorAll('.color-item');
            insideColors.forEach(function (el) {

                var fontColor = color.toLowerCase();
                var bgColor = el.dataset.color.toLowerCase();
                var randomColor = self._getBestColor(fontColor, bgColor);

                el.dataset.color = randomColor.color;
                el.style.backgroundColor = randomColor.color;
                el.dataset.clickablestate = randomColor.clickable;
            });
        },
        _generateColorsDefault: function (targetEl) {
            const insideColors = targetEl.querySelectorAll('.color-item');

            let colorArr = [];
            this._colorDefault.forEach(tempArr => {
                tempArr.forEach((el, i) => {
                    colorArr.push(el.trim());
                });
            });

            insideColors.forEach(function (el, i) {
                let defaultColor = 'c0b782';
                if (colorArr[i] !== undefined) {
                    defaultColor = colorArr[i];
                }

                el.dataset.color = `${defaultColor}`;
                el.style.backgroundColor = `${defaultColor}`;
            });
        },
        _generateBgColors: function (targetEl, color) {
            var self = this;
            this._generateBgColorsDefault(targetEl);

            const insideColors = targetEl.querySelectorAll('.color-item');
            insideColors.forEach(function (el) {

                var bgColor = color.toLowerCase();
                var fontColor = el.dataset.color.toLowerCase();
                var randomColor = self._getBestColor(bgColor, fontColor);

                el.dataset.color = randomColor.color;
                el.style.backgroundColor = randomColor.color;
                el.dataset.clickablestate = randomColor.clickable;
            });
        },
        _generateBgColorsDefault: function (targetEl) {
            const insideColors = targetEl.querySelectorAll('.color-item');

            let colorArr = [];
            this._colorBgDefault.forEach(tempArr => {
                tempArr.forEach((el, i) => {
                    colorArr.push(el.trim());
                });
            });

            insideColors.forEach(function (el, i) {
                let defaultColor = 'c0b782';
                if (colorArr[i] !== undefined) {
                    defaultColor = colorArr[i];
                }

                el.dataset.color = `${defaultColor}`;
                el.style.backgroundColor = `${defaultColor}`;
                el.dataset.clickablestate = true;
            });
        },
        _changeStyle: function (e, color) {

            let target = e.target;
            let parent = target._node.closest(".dropdown-inner");
            let moddleDialog = target._node.closest(".moodle-dialogue");
            let text = parent.getElementsByClassName('text');
            let clickablestate = target.getAttribute("data-clickablestate");

            moddleDialog.classList.add('has-index');
            moddleDialog.classList.add('d-block');
            color = target.getAttribute("data-color");

            if (color && clickablestate === "true") {
                const colorItem = target._node;
                const colorItemParent = colorItem.closest('.wrapper');
                const colorsParent = document.querySelector(".font-colors .colors-inner");
                const bgcolorsParent = document.querySelector(".bgc-colors .colors-inner");
                let propertyType = colorItemParent.getAttribute("data-type");
                if (propertyType === 'color') {
                    let activeColor = colorsParent.getElementsByClassName('active-color');

                    if (activeColor.length > 0) {
                        activeColor[0].classList.remove('active-color');
                    }
                    colorItem.classList.add('active-color');
                    text[0].style.color = color;
                    this.config['color'] = color;
                }
                if (propertyType === 'bg') {
                    let activeColor = bgcolorsParent.getElementsByClassName('active-color');

                    if (activeColor.length > 0) {
                        activeColor[0].classList.remove('active-color');
                    }
                    colorItem.classList.add('active-color');
                    text[0].style.backgroundColor = color;
                    this.config['background-color'] = color;
                }
                if (target._node.closest('.wrapper').dataset.state === 'master') {
                    let targetEl;
                    parent.querySelectorAll('.wrapper').forEach((el) => {
                        if (el.dataset.state.length === 0) {
                            targetEl = el.querySelector('.colors-inner');
                        }
                    });

                    if (propertyType === 'color') {
                        this._generateColors(targetEl, color);
                        let activeColor = bgcolorsParent.getElementsByClassName('active-color');
                        if (activeColor.length > 0) {
                            activeColor[0].classList.remove('active-color');
                        }
                    }

                    if (propertyType === 'bg') {
                        this._generateBgColors(targetEl, color);
                        let activeColor = colorsParent.getElementsByClassName('active-color');
                        if (activeColor.length > 0) {
                            activeColor[0].classList.remove('active-color');
                        }
                    }
                }
            }

            // Default state.
            if (target._node.closest('.radiobtn-wrapper') || target._node.classList.contains('radiobtn-wrapper')) {
                let customRadioBtn = target._node.closest('.radiobtn-wrapper') || target._node;
                if (customRadioBtn.id === 'setFontColor' || customRadioBtn.id === 'setBgColor') {
                    if (target._node.closest('.wrapper').dataset.state !== 'master') {
                        parent.querySelectorAll('.wrapper').forEach((el) => {

                            let targetEl = el.querySelector('.colors-inner');

                            if (el.dataset.type === 'color') {
                                this._generateColorsDefault(targetEl);
                            }

                            if (el.dataset.type === 'bg') {
                                this._generateBgColorsDefault(targetEl);
                            }
                        });

                        // Default text.
                        text[0].style.color = '#000000';
                        this.config['color'] = '#000000';

                        text[0].style.backgroundColor = '#FFFFFF';
                        this.config['background-color'] = '#FFFFFF';
                    }
                }
            }

            if (target._node.id === 'changeSelectedText') {
                this._getColorData();
                text[0].setAttribute('style', '');
            }

            if (target._node.id === 'restoreColorDefaults') {
                text[0].setAttribute('style', '');
                this._generateBgColorsDefault(document.querySelector('.font-colors .colors-inner'));
                this._generateColorsDefault(document.querySelector('.bgc-colors .colors-inner'));

                const textPreviewBlock = document.querySelector('.text');
                textPreviewBlock.innerHTML = textPreviewBlock.dataset.text;
            }

            this.markUpdated();
        },

        _getColorData: function () {
            this.get('host').formatSelectionInlineStyle(this.config);
            for (const prop of Object.getOwnPropertyNames(this.config)) {
                delete this.config[prop];
            }
            this.markUpdated();
        },

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

}, '@VERSION@');