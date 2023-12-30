/* eslint-disable require-jsdoc */
define([
    'jquery',
    'core/ajax',
    'core/str',
    'core/templates',
    'core/notification',
    'core/modal_factory',
    'core/fragment',
    'jqueryui',

], function($, Ajax, str, Templates, Notification, ModalFactory, Fragment) {

    return {

        init: function(uniqueid) {
            var form = $('#sharing_activities_form_' + uniqueid),
                self = this;
            form.on('keydown', 'input[type="text"]', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            });

            form.delegate('[data-descr="addtag"]', 'keydown', function(e) {
                if (e.keyCode === 13 && $(this).val()) {
                    var tag = $('<div class = "tags-item">' + $(this).val() +
                        '<input type = "hidden" name = "tags[]" value = "' + $(this).val() + '"></div>');
                    tag.css("background-color", self.getRandColor());
                    tag.appendTo($(this).parent());
                    tag.on('click', function() {
                        $(this).remove();
                    });
                    $(this).val('');
                }
            });

            form.find('.uploadactivitysubmit').click(function(event) {
                self.uploadActivity(event, form);
            });

            form.find('.uploadactivityclose').click(function(event) {

                // Close modal factory popup.
                self.closeModalFactory(form);
            });

        },

        uploadActivity: function(e, form) {
            let self = this;

            e.preventDefault();

            // Remove errors.
            form.find('.invalid-feedback').hide();

            self.addBtnSpinner(form);

            var serializedForm = form.serializeArray(),
                data = {};

            serializedForm.forEach(function(item) {
                data[item.name] = data[item.name] ? data[item.name] + ',' + item.value : item.value;
            });

            // Serialized selected sections.
            let selected_sections = [];
            form.find('.selected-section-block').each(function(index) {
                if (!$(this).hasClass('hidden')) {
                    selected_sections.push({
                        'cat_id': $(this).data("cat_id"),
                        'course_id': $(this).data("course_id"),
                        'section_id': $(this).data("section_id"),
                    });
                }
            });

            data.selected_sections = selected_sections;

            // Serialized selected competencies.
            let selected_competencies = [];
            form.find('.selected-competency-block').each(function(index) {
                if (!$(this).hasClass('hidden')) {
                    selected_competencies.push({
                        'competency_id': $(this).data("comp_id"),
                        'section_id': $(this).data("section_id"),
                    });
                }
            });

            data.selected_competencies = selected_competencies;

            // Selected_questions.
            const questionsContainer = document.querySelector('div.mod-quiz-edit-content');
            const checkboxes = questionsContainer.querySelectorAll('input.select-multiple-checkbox:checked');
            const selected_questions = [];
            checkboxes.forEach(checkbox => {
                const questionContainer = checkbox.closest('li.activity');
                if (questionContainer) {
                    const activityInstance = questionContainer.querySelector('div.activityinstance');
                    if (activityInstance) {
                        const link = activityInstance.querySelector('a[href*="id="]');
                        if (link) {
                            const questionHref = link.getAttribute('href');
                            const questionIdMatch = questionHref.match(/[?&]id=(\d+)/);
                            if (questionIdMatch) {
                                const questionRealId = questionIdMatch[1];
                                selected_questions.push(questionRealId);
                            }
                        }
                    }
                }
            });

            data.selected_questions = JSON.stringify(selected_questions);

            var parseResponse = function(response) {
                if (response.result) {

                    if (!response.validation) {
                        let firstNameError = '';
                        let errors = JSON.parse(response.errors);
                        $.each(errors, function(index, value) {
                            if (index === 0) {
                                firstNameError = value;
                            }
                            form.find('.error-' + value).show();
                        });
                        // Scroll to first error.
                        var parentModal = form.closest('.modal-body');
                        var uploadActivityOffset = +parentModal.find('.uploadactivity').offset().top * (-1);
                        var targetOffset = parentModal.find('.error-' + firstNameError).closest('.form-group').offset().top * (-1);
                        var result = uploadActivityOffset - targetOffset;
                        parentModal.closest('.modal-body').animate({scrollTop: result}, 500);

                        self.removeBtnSpinner(form);
                        return;
                    } else {

                        // Close modal factory popup.
                        self.closeModalFactory(form);

                        let title = M.util.get_string('eventquestionupload', 'community_sharequestion');
                        str.get_string('question_upload_to_mr', 'community_sharequestion', data).done(function(string) {
                            Templates.render('community_sharequestion/elements/information-popup',
                                {string: string, rootLink: M.cfg.wwwroot})
                                .done(function(html, js) {
                                    self.informationPopup(title, html);
                                })
                                .fail(Notification.exception);
                        });
                    }
                } else {

                    // Close modal factory popup.
                    self.closeModalFactory(form);

                    let title = M.util.get_string('error', 'community_sharequestion');
                    let text = M.util.get_string('system_error_contact_administrator', 'community_sharequestion');
                    self.informationPopup(title, text);
                }
            };

            Ajax.call([{
                methodname: 'community_sharequestion_submit_upload_activity',
                args: {
                    data: JSON.stringify(data)
                },
                done: parseResponse,
                fail: Notification.exception
            }]);
        },

        getRandColor: function() {
            var color = Math.floor(Math.random() * Math.pow(256, 3)).toString(16);
            while (color.length < 6) {
                color = "0" + color;
            }
            return "#" + color;
        },

        informationPopup: function(title, html) {
            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.ALERT,
                title: title,
                body: html
            });

            $.when(modalPromise).then(function(fmodal) {
                fmodal.show();
                return fmodal;
            }).fail(Notification.exception);
        },

        closeModalFactory: function(form) {
            form.parent().parent().parent().find('.close').click();
        },

        /**
         * Show spinner.
         *
         * @param form
         * @method addSpinner
         */
        addBtnSpinner: function(form) {
            form.find('.modalspinner').removeClass('d-none');
            form.find('.modalspinner').addClass('loading');
            form.find('.modalspinner').parent().prop('disabled', true);
        },

        /**
         * Remove spinner.
         *
         * @param form
         * @method addSpinner
         */
        removeBtnSpinner: function(form) {
            form.find('.modalspinner').removeClass('loading');
            form.find('.modalspinner').addClass('d-none');
            form.find('.modalspinner').parent().prop('disabled', false);
        },
    };
});
