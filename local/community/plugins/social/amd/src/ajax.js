define(['jquery', 'core/yui', 'community_social/popup', 'community_social/loadingSpinner', 'core/ajax'], function ($, Y, popup, loading, Ajax) {
    `use strict`;

    let ajax = {

        data: {},

        run: function (callback) {

            var method = this.data.metod;
            delete this.data.metod;

            Ajax.call([{
                methodname: method,
                args: this.data,
                done: function (response) {
                    callback(response);
                },
                fail: function (response) {
                    popup.error(response);
                },
            }]);

        },

        runPopup: function () {

            var method = this.data.metod;
            delete this.data.metod;

            Ajax.call([{
                methodname: method,
                args: this.data,
                done: function (response) {
                    popup.textHead = response.header;
                    popup.text = response.content;
                    popup.show();
                },
                fail: function (response) {
                    popup.error(response);
                },
            }]);

        },

        setHTML: function (callback) {

            loading.show();
            const targetBlock = document.querySelector(this.data.target_block);

            var method = this.data.metod;
            delete this.data.metod;
            delete this.data.target_block;

            Ajax.call([{
                methodname: method,
                args: this.data,
                done: function (response) {
                    popup.remove();
                    targetBlock.innerHTML = response.content;
                    callback(response);
                    loading.remove();
                },
                fail: function (response) {
                    popup.error(response);
                    loading.remove();
                },
            }]);


        },
    };

    return ajax;

});
