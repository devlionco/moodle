
define(['jquery', 'core/ajax', 'core/notification'], function ($, Ajax, Notification) {

    return {
        init: function (selector, qid) {
            $(document).on('click', '.override-trigger-' + qid, function(e) {
                e.preventDefault();
                e.stopPropagation();
                let _this = $(this);
                let data = {
                    categoryid: _this.data('categoryid'),
                    questionattemptid: _this.data('questionattemptid'),
                    questionid: _this.data('questionid'),
                };
                Ajax.call([{
                    methodname: 'qtype_mlnlpessay_set_override',
                    args: data,
                }])[0].done(function(response) {
                    if (response.status) {
                        let data = response.response;
                        data = JSON.parse(data);
                        let container = $('.' + selector + '-' + _this.data('questionid'));
                        container.html(data);
                    } else {
                        Notification.addNotification({
                            message: response.message,
                            type: 'error'
                        });
                    }
                }).fail(Notification.exception);
            });

        },

    };
});
