define([
    'jquery',
    'gradingform_absquestion/tr_question_template',
    'gradingform_absquestion/tr_subq_template',
], function($, TrQ, TrSQ) {
    return class Table {
        constructor(obsItem) {
            if (Table._instance) {
                return Table._instance;
            }
            Table._instance = this;
            this.obsItem = obsItem;
            this.actions();
        }

        async init(state) {
            const data = await this.obsItem.loadTemplate('gradingform_absquestion/table', state);
            let tableEl = document.querySelector('#root_absolute_q #tableBlock');
            tableEl.innerHTML = data;

            // Draw questions
            let selectedTotalQGroup = 0;
            for (let qId = 0; qId < state.questions.length; qId++) {
                const tableEl = document.querySelector('#root_absolute_q #tableBlock table');
                const qRealId = state.questions[qId].id;
                const trRes = TrQ(qId, state, qRealId, state.questions[qId]);
                tableEl.insertAdjacentHTML("beforeend", trRes);

                if (+selectedTotalQGroup < +state.questions[qId].group) {
                    selectedTotalQGroup = +state.questions[qId].group;
                }

                // Draw subquestions
                const subDomEl = document.querySelector(`#root_absolute_q #question_${qId}`);
                for (let i = 0; i < state.questions[qId].subq.length; i++) {
                    const realSubqId = state.questions[qId].subq[i].id;
                    const elSubquestion = state.questions[qId].subq[i];

                    const tr = TrSQ(qId, i, state, realSubqId, elSubquestion);
                    subDomEl.insertAdjacentHTML("beforeend", tr);
                }
            }

            // Selected Total Question item
            const totalQEl = document.querySelector(`#root_absolute_q #total_questions`);
            if (totalQEl.options[state.questions.length]){
                totalQEl.options[state.questions.length].selected = true;
            }

            // Change state.groupsCurrent and selected Total Questions Groups
            const totalQGroupsEl = document.querySelector(`#root_absolute_q #total_questions_groups`);
            if (!state.groupsCurrent) {
                totalQGroupsEl.options[selectedTotalQGroup].selected = true;

                state.groupsCurrent = selectedTotalQGroup;
                this.obsItem.setState(state);

                // Total questions groups remove unnecessary groups
                let groupsSelects = document.querySelector('#root_absolute_q').querySelectorAll('.groups-select');

                if (selectedTotalQGroup > 0) {
                    groupsSelects.forEach(function(el) {
                        for (let i = state.initGroupsArr.length; i > selectedTotalQGroup; i--) {
                            el.removeChild(el.lastChild);
                        }
                    });
                }
            } else {
                totalQGroupsEl.options[state.groupsCurrent].selected = true;
            }

            // Select Grading Methods
            const gradingSelect = document.querySelector(`#root_absolute_q #grading_methods`);
            gradingSelect.options[state.method ? state.method : 0].selected = true;

            // Change grouppas
            this.obsItem.callMethod(state, 'changeGrouppasByDom');
            // This.obsItem.callMethod(state, 'grouppasInitFromState');
            // this.obsItem.callMethod(state, 'grouppasDraw');

            // change output
            await this.obsItem.callMethodReturn(state, 'outputDraw');

            // Check errors
            this.obsItem.callMethodReturn(false, 'allErr');

            this.changeSizeOfSelects();

            // .groups-select show/hide
            this.questionGroupsDisplay(state);

            // manage commits btn disable/enable
            this.manageCommentsDisabled(state);

            // Draft mode or Submitted mode
            this.initDfartMode(state);

            // add data-globalpoint
            this.addDataGlobalPoint(state);
        }

        reopenReset(){
            document.querySelector('#root_absolute_q #tableBlock').innerHTML = "";
            document.querySelector('#root_absolute_q #grouppas').innerHTML = "";
            document.querySelector('#root_absolute_q #save_form').removeAttribute('disabled');
            document.querySelector('#root_absolute_q #submit_form').removeAttribute('disabled');
            this.obsItem.callMethodReturn(true, 'startInitialization');
        }

        addDataGlobalPoint(state){
            let minPoint = Number.POSITIVE_INFINITY;
            for (let key in state.questions) {
                if ('maxpoints' in state.questions[key]){
                    const maxpoint = +state.questions[key].maxpoints;
                    if (minPoint > maxpoint){
                        minPoint = maxpoint;
                    }
                }
                for (let subKey in state.questions[key].subq) {
                    if ('maxpoint' in state.questions[key].subq[subKey]){
                        const maxpoint = +state.questions[key].subq[subKey].maxpoint;
                        if (minPoint > maxpoint){
                            minPoint = maxpoint;
                        }
                    }
                }
            }

            state.globalMaxGrade = {
                minPoint,
                global: false
            };
            this.obsItem.setState(state);
        }

        initDfartMode(state){
            let draftLabelEl = document.querySelector("#root_absolute_q .draft-label");

            if (state.freese){
                draftLabelEl.style.background = "#efcfcf";
                draftLabelEl.innerHTML = state.translate.freese_warning;
                return;
            }

            if (state.questions.length === 0){
                draftLabelEl.innerHTML = "";
                draftLabelEl.style.background = "#ffffff";
            } else {
                draftLabelEl.innerHTML = state.translate.ready_to_submit;
                draftLabelEl.style.background = "#cfefcf";
            }
        }

        changeDraftMode(){
            let state = this.obsItem.getState();
            let draftLabelEl = document.querySelector("#root_absolute_q .draft-label");

            if (state.freese){
                draftLabelEl.style.background = "#efcfcf";
                draftLabelEl.innerHTML = state.translate.freese_warning;
                return;
            }

            draftLabelEl.innerHTML = state.translate.draft_mode;
            draftLabelEl.style.background = "#efcfcf";
        }

        manageCommentsDisabled(state){
            const btnManageComments = document.querySelector("#root_absolute_q .manage-comments");
            if (!btnManageComments){
                return;
            }

            function setAttr(disableBtnManage){
                if (!disableBtnManage){
                    btnManageComments.setAttribute('disabled', true);
                } else {
                    btnManageComments.removeAttribute('disabled');
                }
            }

            if (!state.questions){
                setAttr(false);
                return;
            }

            if (state.questions.length === 0){
                setAttr(false);
                return;
            }

            let visibleBtnManage = false;
            state.questions.forEach((obj) => {
                if ('id' in obj){
                    visibleBtnManage = true;
                }
            });
            setAttr(visibleBtnManage);
        }

        questionGroupsDisplay(state){
            let groupsSelectEls = document.querySelectorAll('#root_absolute_q .groups-select');
            if (state.groupsCurrent === 0){
                groupsSelectEls.forEach(function(el) {
                    el.style.display = "none";
                });
            } else {
                groupsSelectEls.forEach(function(el) {
                    el.style.display = "inline";
                });
            }
        }

        changeQuestionGroupsDom(state){
            const el = document.querySelector('#root_absolute_q #total_questions_groups');
            let newArr = [...state.initGroupsArr];
            newArr.length = state.questions.length + 1;

            let current = state.groupsCurrent;
            let template = `<option value="0" ${0 === current ? "selected" : ""}>${state.translate.total_q_groups_0}</option>`;

            newArr.forEach((el) => {
                // selected="selected"
                if (el.id === 0){
                    template = `<option 
                        value="0" ${el.id === current ? "selected" : ""}>${state.translate.total_q_groups_0}</option>`;
                } else {
                    template += `<option value="${el.id}" ${el.id === current ? "selected" : ""}>${el.id}</option>`;
                }
            });

            el.innerHTML = template;

            if (current > state.questions.length){
                state.groupsCurrent = 0;
                this.obsItem.setState(state);

                this.changeGroupsSelectedDOM({value: 0, state});
            }
        }

        changeGroupsSelectedDOM(obj){
            let value = +obj.value;
            let state = obj.state;

            let groupsSelects = document.querySelector('#root_absolute_q').querySelectorAll('.groups-select');

            groupsSelects.forEach(function(el, index) {
                let template = '';
                let group = state.questions[index].group;
                for (let i = 0; i <= value; i++) {
                    template += `<option value="${i}" ${i === group ? "selected" : ""}>${i === 0 ? "" : i}</option>`;
                }
                el.innerHTML = template;
            });

            state.groupsCurrent = value;
            this.obsItem.setState(state);
        }

        changeQuestions(state) {
            const questions = state.questions;
            let qPast = +state.questionsLPast;

            const qLength = questions.length;
            const tableEl = document.querySelector('#root_absolute_q #tableBlock table');

            if (!qPast) {
                qPast = 0;
            }

            if (qPast === qLength) {
                return false;
            }

            if (qPast > qLength) {
                // Remove some questions from DOM
                for (let i = qLength; i < qPast; i++) {
                    tableEl.removeChild(tableEl.lastChild);
                }
            }

            if (qPast < qLength) {
                // Add questions to DOM
                for (let i = qPast; i < qLength; i++) {
                    const tr = TrQ(i, state);
                    tableEl.insertAdjacentHTML("beforeend", tr);
                }
            }

            state.questionsLPast = qLength;
            this.obsItem.setState(state);
        }

        changeSubQuestions(state) {
            const subQuestions = state.questions[state.questionCurrent].subq;
            let subPast = +state.questions[state.questionCurrent].subqLPast;

            const tableEl = document.querySelector(`#question_${state.questionCurrent}`);

            if (!subPast) {
                subPast = 0;
            }

            if (subPast === subQuestions.length) {
                return false;
            }

            if (subPast > subQuestions.length) {
                // Remove some subquestions from DOM
                for (let i = subQuestions.length; i < subPast; i++) {
                    tableEl.removeChild(tableEl.lastChild);
                }
            }

            if (subPast < subQuestions.length) {
                // Add subquestions to DOM
                for (let i = subPast; i < subQuestions.length; i++) {
                    const tr = TrSQ(+state.questionCurrent, i, state);
                    tableEl.insertAdjacentHTML("beforeend", tr);
                }
            }

            state.questions[state.questionCurrent].subqLPast = subQuestions.length;
            this.obsItem.setState(state);
        }

        setGroupMaxPoint(groupInit, qmaxInit) {
            if (!groupInit || !qmaxInit) {
                return;
            }

            let questions = document.querySelectorAll('#root_absolute_q #tableBlock tbody');

            questions.forEach(function(el) {
                const qmaxpoinValue = el.querySelector('.max-point-input');
                const groupSelectEl = el.querySelector('.groups-select');
                const group = groupSelectEl.options[groupSelectEl.selectedIndex].text;

                if (groupInit === group) { // && id !== parrentId
                    qmaxpoinValue.value = +qmaxInit;
                    // SubPointsInput.forEach((el) => {
                    //     el.value = 0;
                    // })
                }
            });
        }

        changeSizeOfSelect(el) {
            const text = $(el).find('option:selected').text();
            const $aux = $('<select/>').append($('<option/>').text(text));
            $(el).after($aux);
            $(el).width($aux.width());
            $aux.remove();
        }

        changeSizeOfSelects() {
            let self = this;
            let subQSelects = document.querySelectorAll("#root_absolute_q .sub-questions");

            subQSelects.forEach((el) => {
                // Change size of select
                self.changeSizeOfSelect(el);
            });
        }

        async maxPointChange(value, addCommentEl){
            let self = this;
            let state = self.obsItem.getState();
            let inputCorrectValue = self.obsItem.callMethodReturn({
                value: value,
                zero: false,
            }, 'isIntPositiveNum');

            if (inputCorrectValue){
                const saveForm = await self.obsItem.callMethodReturn(0, 'saveAndContinues');
                if (saveForm) {
                    console.log('saved');
                    addCommentEl.setAttribute('data-point', value);

                    const qId = +addCommentEl.getAttribute('data-questionid');

                    let minPoint = Number.POSITIVE_INFINITY;
                    state.questions.forEach(function(qEl) {
                        if (qEl.id === qId){
                            qEl.maxpoints = value;
                        }
                        const maxpoint = +qEl.maxpoints;
                        if (minPoint > maxpoint){
                            minPoint = maxpoint;
                        }
                        qEl.subq.forEach(function(sEl) {
                            if (sEl.id === qId){
                                sEl.maxpoint = value;
                            }
                            const maxpoint = +sEl.maxpoint;
                            if (minPoint > maxpoint){
                                minPoint = maxpoint;
                            }
                        });
                    });

                    state.globalMaxGrade.minPoint = minPoint;
                    self.obsItem.setState(state);

                    self.obsItem.callMethodReturn(false, "toastSuccessShow");
                } else {
                    console.log('error max-point-input');
                }
            }
        }

        actions() {
            let self = this;

            // Add subquestions
            this.obsItem.on('#root_absolute_q', 'change', '.sub-questions', async function() {
                let state = self.obsItem.getState();
                let value = +this.value;

                let questionId = this.getAttribute('data-question');

                if (!state.questions[+questionId].subq) {
                    state.questions[+questionId].subq = [];
                }
                state.questions[+questionId].subq = self.obsItem.pushItemsToArr(
                    value,
                    state.questions[questionId].subq,
                    {
                        maxpoint: 0,
                        info: "",
                    }
                );
                state.questionCurrent = questionId;

                self.obsItem.callMethod(state, 'changeSubQuestions');

                // Check errors
                self.obsItem.callMethodReturn(false, 'allErr');

                self.changeSizeOfSelect(this);

                self.obsItem.callMethodReturn(false, 'changeDraftMode');

                const saveForm = await self.obsItem.callMethodReturn(0, 'saveAndContinues');
                if (saveForm) {
                    self.obsItem.callMethodReturn(false, "reopenReset");
                }
            });

            // Change Question Groups
            this.obsItem.on('#root_absolute_q', 'change', '.groups-select', function() {
                let state = self.obsItem.getState();
                const group = this.options[this.selectedIndex].text;
                const parrent = this.closest('tbody');
                const qmax = parrent.querySelector('.max-point-input');
                // Const parrentId = parrent.getAttribute('id');

                if (qmax && qmax.value && group) {
                    self.setGroupMaxPoint(group, +qmax.value);
                }

                self.obsItem.callMethod(state, 'changeGrouppasByDom');
                self.obsItem.callMethod(state, 'outputDraw');

                // Check errors
                self.obsItem.callMethodReturn(false, 'allErr');
            });

            // Qmax question
            this.obsItem.on('#root_absolute_q', 'input', '.max-point-input', function() {
                let state = self.obsItem.getState();
                const parrent = this.closest('tbody');

                // Check errors
                // const qmaxHasErr = self.obsItem.callMethodReturn({ parrent, this: this }, 'errThisQuestionAndSubq');
                // if (qmaxHasErr) {
                //     // self.obsItem.callMethod(state, 'outputDraw');
                //     return;
                // }

                const groupEl = parrent.querySelector('.groups-select');
                const group = groupEl.options[groupEl.selectedIndex].text;
                // Const parrentId = parrent.getAttribute('id');

                const addCommentEl = parrent.querySelector('.add-comments');

                if (group) {
                    self.setGroupMaxPoint(group, +this.value);
                }

                self.obsItem.callMethod(state, 'changeGrouppasByDom');
                self.obsItem.callMethod(state, 'outputDraw');

                self.obsItem.callMethodReturn(false, 'allErr');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');

                self.maxPointChange(+this.value, addCommentEl);
            });

            // Bonus change
            this.obsItem.on('#root_absolute_q', 'change', '.bonus-checkbox', function() {
                let state = self.obsItem.getState();
                self.obsItem.callMethod(state, 'changeGrouppasByDom');
                self.obsItem.callMethod(state, 'outputDraw');

                self.obsItem.callMethodReturn(false, 'allErr');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });

            // Sub points change
            this.obsItem.on('#root_absolute_q', 'input', '.sub-points-input', function() {
                let state = self.obsItem.getState();
                // Check errors
                self.obsItem.callMethodReturn(false, 'allErr');

                self.obsItem.callMethod(state, 'outputDraw');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');

                const parrent = this.closest('tr');

                const addCommentEl = parrent.querySelector('.add-comments');

                self.maxPointChange(+this.value, addCommentEl);
            });
        }
    };
});