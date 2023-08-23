/* eslint-disable no-debugger */
/* eslint-disable no-trailing-spaces */
/* eslint-disable no-console */
import $ from 'jquery';
import * as Str from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Notification from 'core/notification';
import * as studentsTableActions from 'quiz_advancedoverview/studentsTableActions';

const PILLS = {};
const SELECTORS = {
    SelectGroupBtn: '#advancedoverview_groupid',
    dynamicBlock: '.dynamic-block',
    studentsActionsBlock: '#studentsActionsBlock',
    btns: '#studentsActionsBlock button[type="button"]',
    studentsActionsBtn: '#studentsActionsBtn',
    studentsActionsCollapse: '#studentsActionsCollapse',
    pillsAreaInner: '#pillsAreaInner',
    clearPillsArea: '#clearPillsArea',
    checkboxes: '#studentsActionsCollapse input[type="checkbox"]',
    sendMessage: '#sendMessage',
    recalculateGrades: '#recalculateGrades',
    deleteAttemtps: '#deleteAttemtps',
    closingResponseAttempts: '#closingResponseAttempts',
    selectedStudents: '#selectedStudents',
    zerostr: '#zerostr',
    counterstr: '#counterstr',
    studentstableNavFilter: '#studentstableNavFilter',
};

const changeBtnState = (target) => {
    let state = (target.getAttribute('aria-pressed') === 'true') ? 'false' : 'true';
    target.setAttribute('aria-pressed', state);
    target.classList.toggle("active");

    // Check is target inside btngroup and set aria-pressed="false" for other btn in group.
    if (target.closest('.nav-tabs')) {
        let btnGroup = target.closest('.nav-tabs');
        let ingroupBtns = btnGroup.querySelectorAll('button[type="button"]');
        ingroupBtns.forEach((el) => {
            if (el !== target) {
                el.classList.remove("active");
                el.setAttribute('aria-pressed', 'false');
            }
        });
    }
};

const addPill = (data, target) => {
    const template = document.createElement('a');
    template.innerHTML = data.text;
    template.classList.add('pill', 'badge', 'badge-light', 'm-1', 'p-2');
    template.setAttribute('id', 'pill' + data.id);
    template.setAttribute('data-id', data.id);
    template.setAttribute('role', 'button');
    template.setAttribute('data-pilltype', data.type);

    const closeIcon = document.createElement('i');
    closeIcon.classList.add('fa', 'fa-times', 'pl-2');
    closeIcon.setAttribute('data-id', data.id);
    closeIcon.setAttribute('aria-hidden', 'true');
    template.appendChild(closeIcon);

    target[0].appendChild(template);
    // $(SELECTORS.clearPillsArea).show();
};

const removePillByClick = (target) => {
    if (target.classList.contains('pill') || target.closest('.pill.badge')) {
        let id = target.dataset.id || target.closest('.pill.badge').dataset.id;
        let pilltype = target.dataset.pilltype || target.closest('.pill.badge').dataset.pilltype;
        removePill(id, pilltype);
    }
};

const removePill = (id, pilltype) => {
    const element = document.querySelector(`[data-id="${id}"]`);

    $(SELECTORS.checkboxes).each((i) => {
        if ($(SELECTORS.checkboxes)[i].id === id) {

            if (pilltype !== undefined) {
                $(SELECTORS.checkboxes)[i].checked = true;
                $(SELECTORS.checkboxes).eq(i).trigger('click');
            } else {
                $(SELECTORS.checkboxes)[i].checked = false;
                element.remove();
                delete PILLS[id];
            }
        }
    });
};

const removeAllPills = (e) => {
    e.preventDefault();
    for (const key in PILLS) {
        delete PILLS[key];
    }
    $(SELECTORS.checkboxes).each((i) => {
        $(SELECTORS.checkboxes)[i].checked = false;
    });
    $(SELECTORS.pillsAreaInner).empty();
    $(SELECTORS.clearPillsArea).hide();
};


export const TEMPDATA = {};
export const init = function (cmid, courseid, quizid) {
    let self = this;

    self.TEMPDATA.cmid = cmid;
    self.TEMPDATA.courseid = courseid;
    self.TEMPDATA.quizid = quizid;


    // Event on change group select.
    $(SELECTORS.SelectGroupBtn).closest('.dropdown').find('.dropdown-item').on('click', function (e) {
        let value = $(e.currentTarget).data('value');
        let name = $(e.currentTarget).data('name');
        $(SELECTORS.SelectGroupBtn).data('value', value);
        $(SELECTORS.SelectGroupBtn).data('name', name);
        $(SELECTORS.SelectGroupBtn).find('span').html(name);
        $(SELECTORS.SelectGroupBtn).addClass('selected');
        self.renderDynamicBlock(cmid, value);
    });

    $(SELECTORS.btns).on('click', function (e) {
        changeBtnState(e.currentTarget);
    });

    // Set filters checkboxes to false first load.
    $(SELECTORS.checkboxes).each((i) => {
        $(SELECTORS.checkboxes)[i].checked = false;
    });

    $(document).on('change', SELECTORS.checkboxes, function (e) {

        const data = {
            text: e.target.dataset.label,
            id: e.target.id,
            type: e.target.dataset.type
        };

        if (PILLS[data.id]) {
            removePill(data.id);
        } else {
            PILLS[data.id] = {};
            PILLS[data.id].type = data.type;
            PILLS[data.id].text = data.text;
            addPill(data, $(SELECTORS.pillsAreaInner));
        }
        if (Object.keys(PILLS).length > 0) {
            $(SELECTORS.clearPillsArea).show();
        } else {
            $(SELECTORS.clearPillsArea).hide();
        }
    });

    $(document).on('click', SELECTORS.pillsAreaInner, function (e) {
        removePillByClick(e.target);
    });

    $(document).on('click', SELECTORS.clearPillsArea, function (e) {
        removeAllPills(e);
    });

    $(document).on('click', SELECTORS.sendMessage, function () {
        let users = [];
        let data = self.TEMPDATA.rowData;
        if (data.length > 0) {
            data.forEach(el => {
                users.push(el.userid);
            });
        }
        self.showMessagePopup(users);
    });

    $(document).on('change', '#scoreDisplayToggler', function () {
        $('#students-table').toggleClass('score-display');
    });
    $(document).on('click', 'a[target="popup"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let link = e.currentTarget.href;

        let params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,
width=600,height=600,left=100,top=100`;

        open(link, '', params);

    });
    $(document).on('click', SELECTORS.recalculateGrades, function () {
        let attemptids = '';
        let data = self.TEMPDATA.rowData;
        if (data.length > 0) {
            data.forEach((el) => {
                if (el.attemptid) {
                    attemptids += el.attemptid + ',';
                }
            });
        }
        attemptids = attemptids.slice(0, -1);
        if (attemptids) {
            self.regradaAttemtps(+self.TEMPDATA.cmid, +self.TEMPDATA.courseid, +self.TEMPDATA.quizid, attemptids);
        } else {
            Str.get_strings([
                { key: 'reportregrade', component: 'quiz_advancedoverview' },
                { key: 'selecteditemswrong', component: 'quiz_advancedoverview' },
            ]).done(function (strings) {
                var modalPromise = ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                    title: strings[0],
                    body: strings[1]
                });

                $.when(modalPromise).then(function (fmodal) {
                    return fmodal;
                }).done(function (modal) {
                    modal.show();
                }).fail(Notification.exception);
            });
        }
    });


    $(document).on('click', SELECTORS.deleteAttemtps, function () {
        let attemptids = '';
        let data = self.TEMPDATA.rowData;
        if (data.length > 0) {
            data.forEach((el) => {
                if (el.attemptid) {
                    attemptids += el.attemptid + ',';
                }
            });
        }
        attemptids = attemptids.slice(0, -1);
        if (attemptids) {
            self.deleteAttemtps(+self.TEMPDATA.cmid, +self.TEMPDATA.courseid, +self.TEMPDATA.quizid, attemptids);
        } else {
            Str.get_strings([
                { key: 'deletingattempts', component: 'quiz_advancedoverview' },
                { key: 'selecteditemswrong', component: 'quiz_advancedoverview' },
            ]).done(function (strings) {
                var modalPromise = ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                    title: strings[0],
                    body: strings[1]
                });

                $.when(modalPromise).then(function (fmodal) {
                    return fmodal;
                }).done(function (modal) {
                    modal.show();
                }).fail(Notification.exception);
            });
        }
    });
    $(document).on('click', SELECTORS.closingResponseAttempts, function () {
        let attemptids = '';
        let data = self.TEMPDATA.rowData;
        if (data.length > 0) {
            data.forEach((el) => {
                if (el.attemptid) {
                    attemptids += el.attemptid + ',';
                }
            });
        }
        attemptids = attemptids.slice(0, -1);
        if (attemptids) {
            self.closingResponseAttempts(+self.TEMPDATA.cmid, +self.TEMPDATA.courseid, +self.TEMPDATA.quizid, attemptids);
        } else {
            Str.get_strings([
                { key: 'closingresponseattempts', component: 'quiz_advancedoverview' },
                { key: 'selecteditemswrong', component: 'quiz_advancedoverview' },
            ]).done(function (strings) {
                var modalPromise = ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                    title: strings[0],
                    body: strings[1]
                });

                $.when(modalPromise).then(function (fmodal) {
                    return fmodal;
                }).done(function (modal) {
                    modal.show();
                }).fail(Notification.exception);
            });
        }
    });

};

export const renderDynamicBlock = function (cmid, groupid) {
    Ajax.call([{
        methodname: 'quiz_advancedoverview_render_dynamic_block',
        args: {
            cmid: cmid,
            groupid: groupid,
            config: '',
        },
        done: function (response) {
            let data = JSON.parse(response);

            // Render dinamic block.
            Templates.render('quiz_advancedoverview/dynamic_block', data)
                .done(function (html, js) {
                    studentsTableActions.setAnonToggl(data.config.anonymous_mode);
                    Templates.replaceNodeContents(SELECTORS.dynamicBlock, html, js);
                })
                .fail(Notification.exception);
        },
        fail: Notification.exception
    }]);
};
export const showMessagePopup = function (users) {

    if (users.length === 0) {
        // Nothing to do.
        return $.Deferred().resolve().promise();
    }
    var titlePromise = null;
    if (users.length === 1) {
        titlePromise = Str.get_string('sendbulkmessagesingle', 'core_message');
    } else {
        titlePromise = Str.get_string('sendbulkmessage', 'core_message', users.length);
    }

    return $.when(
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            body: Templates.render('core_user/send_bulk_message', {})
        }),
        titlePromise
    ).then(function (modal, title) {
        this.modal = modal;
        this.modal.setTitle(title);
        this.modal.setSaveButtonText(title);

        let textarea = this.modal.getRoot().find('textarea');
        let submitBtn = this.modal.getRoot().find('.btn[data-action="save"]');
        submitBtn.prop('disabled', 'true');
        textarea.on('keypress', () => {
            submitBtn.prop('disabled', textarea.val().trim().length > 0 ? false : true);
        });

        this.modal.getRoot().on(ModalEvents.hidden, function () {
            this.modal.getRoot().remove();
        }.bind(this));

        this.modal.getRoot().on(ModalEvents.shown, function () {
            textarea.focus();
        });

        this.modal.getRoot().on(ModalEvents.save, this.submitSendMessage.bind(this, users));

        this.modal.show();

        return this.modal;
    }.bind(this));
};
export const submitSendMessage = function (users) {

    var messageText = this.modal.getRoot().find('form textarea').val();

    var messages = [],
        i = 0;

    for (i = 0; i < users.length; i++) {
        messages.push({ touserid: users[i], text: messageText });
    }

    return Ajax.call([{
        methodname: 'core_message_send_instant_messages',
        args: { messages: messages }
    }])[0].then(function (messageIds) {
        if (messageIds.length === 1) {
            return Str.get_string('sendbulkmessagesentsingle', 'core_message');
        } else {
            return Str.get_string('sendbulkmessagesent', 'core_message', messageIds.length);
        }
    }).then(function (msg) {
        Notification.addNotification({
            message: msg,
            type: "success"
        });
        return true;
    }).catch(Notification.exception);
};
export const regradaAttemtps = function (cmid, courseid, quizid, attemptids) {
    let self = this;

    Str.get_strings([
        { key: 'reportregrade', component: 'quiz_advancedoverview' },
        { key: 'areyoushure', component: 'quiz_advancedoverview' },
        { key: 'execute', component: 'quiz_advancedoverview' },
    ]).done(function (strings) {
        var modalPromise = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: strings[0],
            body: strings[1]
        });

        $.when(modalPromise).then(function (fmodal) {

            fmodal.setSaveButtonText(strings[2]);

            // Handle save event.
            fmodal.getRoot().on(ModalEvents.save, function () {

                Ajax.call([{
                    methodname: 'quiz_advancedoverview_regrade_attempts',
                    args: {
                        cmid: cmid,
                        courseid: courseid,
                        quizid: quizid,
                        attemptids: attemptids,
                    },
                    done: function (response) {
                        if (response) {
                            self.renderDynamicBlock(cmid, null);
                        }
                    },
                    fail: Notification.exception
                }]);

            });

            return fmodal;
        }).done(function (modal) {
            modal.show();
        }).fail(Notification.exception);
    });
};
export const deleteAttemtps = function (cmid, courseid, quizid, attemptids) {
    let self = this;

    Str.get_strings([
        { key: 'deletingattempts', component: 'quiz_advancedoverview' },
        { key: 'areyoushure', component: 'quiz_advancedoverview' },
        { key: 'execute', component: 'quiz_advancedoverview' },
    ]).done(function (strings) {
        var modalPromise = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: strings[0],
            body: strings[1]
        });

        $.when(modalPromise).then(function (fmodal) {

            fmodal.setSaveButtonText(strings[2]);

            // Handle save event.
            fmodal.getRoot().on(ModalEvents.save, function () {

                Ajax.call([{
                    methodname: 'quiz_advancedoverview_delete_attempts',
                    args: {
                        cmid: cmid,
                        courseid: courseid,
                        quizid: quizid,
                        attemptids: attemptids,
                    },
                    done: function (response) {
                        if (response) {
                            self.renderDynamicBlock(cmid, null);
                        }
                    },
                    fail: Notification.exception
                }]);

            });

            return fmodal;
        }).done(function (modal) {
            modal.show();
        }).fail(Notification.exception);
    });
};
export const closingResponseAttempts = function (cmid, courseid, quizid, attemptids) {
    let self = this;

    Str.get_strings([
        { key: 'closingresponseattempts', component: 'quiz_advancedoverview' },
        { key: 'areyoushure', component: 'quiz_advancedoverview' },
        { key: 'execute', component: 'quiz_advancedoverview' },
    ]).done(function (strings) {
        var modalPromise = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: strings[0],
            body: strings[1]
        });

        $.when(modalPromise).then(function (fmodal) {

            fmodal.setSaveButtonText(strings[2]);

            // Handle save event.
            fmodal.getRoot().on(ModalEvents.save, function () {

                Ajax.call([{
                    methodname: 'quiz_advancedoverview_close_attempts',
                    args: {
                        cmid: cmid,
                        courseid: courseid,
                        quizid: quizid,
                        attemptids: attemptids,
                    },
                    done: function (response) {
                        if (response) {
                            self.renderDynamicBlock(cmid, null);
                        }
                    },
                    fail: Notification.exception
                }]);

            });

            return fmodal;
        }).done(function (modal) {
            modal.show();
        }).fail(Notification.exception);
    });
};
export const changeStudentActionState = function (state) {
    $(SELECTORS.studentsActionsBtn)[0].disabled = state;
};
export const setSelectedStudentsStr = function (numberOfRows) {
    if (numberOfRows > 0) {
        $(SELECTORS.zerostr).hide();
        Str.get_string('selectedstudents', 'quiz_advancedoverview', numberOfRows).then(function (result) {
            $(SELECTORS.counterstr)[0].innerHTML = result;
            return result;
        }).fail(Notification.exception);
        $(SELECTORS.counterstr).show();
    } else {
        $(SELECTORS.zerostr).show();
        $(SELECTORS.counterstr).hide();
    }
};

