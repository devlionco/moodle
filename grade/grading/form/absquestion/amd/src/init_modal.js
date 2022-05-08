define(['jquery'], function($) {
    return class InitModal {
        constructor(obsItem) {
            if (InitModal._instance) {
                return InitModal._instance;
            }
            InitModal._instance = this;
            this.obsItem = obsItem;
            this.actions();
        }

        async init(state) {
            let qtotalmaxArr = [];
            for (let i = 1; i <= state.qtotalmax; i++) {
                qtotalmaxArr.push({
                    key: i
                });
            }
            state = {...state, ...{qtotalmaxArr}};

            if (!state.reopen){
                const data = await this.obsItem.loadTemplate('gradingform_absquestion/modal', state);
                document.querySelector('#root_absolute_q').innerHTML = data;
            }

            if (!state.reopen){
                $("#root_absolute_q #main_modal").modal({
                    backdrop: 'static',
                    keyboard: false
                });
            }
        }

        actions() {
            let self = this;

            // Questions
            this.obsItem.on('#root_absolute_q', 'change', '#total_questions', async function() {
                let state = self.obsItem.getState();
                let value = +this.value;

                state.questions = self.obsItem.pushItemsToArr(
                    value,
                    state.questions,
                    state.initQuestion
                );

                state.totalQuestions = state.questions.length;

                self.obsItem.callMethod(state, 'changeQuestions');

                self.obsItem.callMethodReturn(state, 'changeQuestionGroupsDom');

                self.obsItem.callMethod(state, 'changeGrouppasByDom');

                self.obsItem.callMethod(state, 'outputDraw');

                self.obsItem.callMethod(state, 'questionGroupsDisplay');

                // Check errors
                self.obsItem.callMethodReturn(false, 'allErr');

                self.obsItem.callMethod(state, 'manageCommentsDisabled');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');

                const saveForm = await self.obsItem.callMethodReturn(0, 'saveAndContinues');
                if (saveForm) {
                    self.obsItem.callMethodReturn(false, "reopenReset");
                }
            });

            // Total Questions Groups change
            this.obsItem.on('#root_absolute_q', 'change', '#total_questions_groups', function() {
                let state = self.obsItem.getState();

                const value = +this.value;
                self.obsItem.callMethodReturn({value, state}, 'changeGroupsSelectedDOM');

                // Grouppas change
                self.obsItem.callMethod(state, 'changeGrouppasByDom');

                self.obsItem.callMethod(state, 'outputDraw');

                // Check errors
                self.obsItem.callMethodReturn(false, 'allErr');

                // show/hide Question Groups
                self.obsItem.callMethod(state, 'questionGroupsDisplay');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });

            // Cancel modal yes
            this.obsItem.on('#root_absolute_q', 'click', '#cancel_modal_yes', function() {
                // Close main popup
                $("#root_absolute_q #main_modal").modal('hide');
            });

            // Save form
            this.obsItem.on('#root_absolute_q', 'click', '#save_form', async function() {
                let submBtn = document.querySelector('#root_absolute_q #submit_form');
                let saveBtn = document.querySelector('#root_absolute_q #save_form');
                submBtn.setAttribute('disabled', true);
                saveBtn.setAttribute('disabled', true);

                const saveForm = await self.obsItem.callMethodReturn(0, 'saveAndContinues');

                if (saveForm) {
                    self.obsItem.callMethodReturn(false, 'toastSuccessShow');

                    self.obsItem.callMethodReturn({
                        method: 'hidden.bs.toast',
                        fn: () => {
                            self.obsItem.callMethodReturn(false, "reopenReset");
                        }
                    }, 'toastHandle');
                }
            });

            // Grading methods
            this.obsItem.on('#root_absolute_q', 'change', '#grading_methods', function() {
                let state = self.obsItem.getState();
                let value = +this.value;
                state.method = value;
                self.obsItem.setState(state);

                self.obsItem.callMethodReturn(false, 'allErr');
                self.obsItem.callMethod(state, 'outputDraw');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });

            // Submit form
            this.obsItem.on('#root_absolute_q', 'click', '#submit_form', async function() {
                let err = self.obsItem.callMethodReturn(false, 'allErr');
                if (err) {
                    $('#root_absolute_q #error_modal').modal('show');
                    return;
                }

                const saveForm = await self.obsItem.callMethodReturn(1, 'saveAndContinues');

                if (saveForm) {
                    self.obsItem.callMethodReturn(false, 'toastSuccessShow');

                    self.obsItem.callMethodReturn({
                        method: 'hidden.bs.toast',
                        fn: () => {
                            // Close main popup
                            $("#root_absolute_q #main_modal").modal('hide');
                        }
                    }, 'toastHandle');
                }

            });

            // Error_modal_yes
            this.obsItem.on('#root_absolute_q', 'click', '#error_modal_yes', async function() {
                let submBtn = document.querySelector('#root_absolute_q #submit_form');
                let saveBtn = document.querySelector('#root_absolute_q #save_form');
                submBtn.setAttribute('disabled', true);
                saveBtn.setAttribute('disabled', true);

                const saveForm = await self.obsItem.callMethodReturn(0, 'saveAndContinues');

                if (saveForm) {
                    self.obsItem.callMethodReturn(false, 'toastSuccessShow');

                    self.obsItem.callMethodReturn({
                        method: 'hidden.bs.toast',
                        fn: () => {
                            // Close main popup
                            $("#root_absolute_q #main_modal").modal('hide');
                        }
                    }, 'toastHandle');
                }
            });
        }
    };
});