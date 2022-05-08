define([
    'jquery',
    'core/ajax',
    'core/templates',
], function($, Ajax, Templates) {
    return class Manage {
        constructor(obsItem) {
            if (Manage._instance) {
                return Manage._instance;
            }
            Manage._instance = this;
            this.obsItem = obsItem;
            this.state = obsItem.getState();
            this.dataObj = {};
            this.deleteCommentId = 0;
            this.actions();
        }

        async manageGetComments(sequence) {
            return await Ajax.call([{
                methodname: 'gradingform_absquestion_get_comments',
                args: {
                    'assignid': Number(this.state.assignId),
                    'sequence': Number(sequence),
                }
            }])[0];
        }

        stripHtml(html) {
            let tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        }

        manageClearAllAtto() {
            let allTextareas = document.querySelectorAll("#root_absolute_q .textarea-i-block div");

            allTextareas.forEach((el) => {
                el.innerHTML = '';
            });
        }

        async manageUpdate() {
            let self = this;
            let state = self.obsItem.getState();

            const promise = Ajax.call([
                {methodname: 'gradingform_absquestion_get_comments', args: {
                    assignid: state.assignid
                }}
            ])[0].done((data) => {
                // eslint-disable-next-line no-console
                console.log(data);
                return data;
            }).fail(function(err) {
                // eslint-disable-next-line no-console
                console.log(err);
                return false;
            });

            let data = await promise;

            if (!data) {
                return;
            }

            console.log('data==>', data);

            self.dataObj = {};
            let dataComments = {};
            data.forEach((obj) => {
                let qid = obj.qid;
                let comment = {
                    id: obj.id,
                    text: obj.text,
                    isglobal: obj.isglobal,
                    points: obj.points,
                };

                if (!self.dataObj[qid]) {
                    self.dataObj[qid] = {};
                    dataComments[qid] = [];
                }
                self.dataObj[qid][obj.id] = comment;
                dataComments[qid].push(comment);
            });

            console.log('dataObj==>', self.dataObj);

            state.questions.forEach(function(qEl, indexQ) {
                if (state.questions[indexQ].comments){
                    state.questions[indexQ].comments.length = 0;
                }
                state.questions[indexQ].questionId = qEl.id;
                if (dataComments[qEl.id]){
                    state.questions[indexQ].comments = dataComments[qEl.id];
                }
                qEl.subq.forEach(function(sEl, indexSq) {
                    state.questions[indexQ].subq[indexSq].qOrder = indexQ + 1;
                    if (state.questions[indexQ].subq[indexSq].subqComments){
                        state.questions[indexQ].subq[indexSq].subqComments.length = 0;
                    }
                    state.questions[indexQ].subq[indexSq].subqId = state.questions[indexQ].subq[indexSq].id;
                    if (dataComments[sEl.id]){
                        state.questions[indexQ].subq[indexSq].subqComments = dataComments[sEl.id];
                    }
                });
            });

            const body = await Templates.render('gradingform_absquestion/manage', {data: state.questions});

            let el = document.querySelector("#root_absolute_q #manage-modal-body");
            el.innerHTML = body;
        }

        async checkAccumulateGrade(params) {
            let self = this;
            let maxPoint = params.point;
            let questionId = params.questionId;
            let pointsInputEl = $("#root_absolute_q .points-add-comment");

            let value = pointsInputEl.val();

            let saveBtn = document.querySelector(`#root_absolute_q .comment_disable_btn[data-questionid="${questionId}"]`);

            let errEl = document.querySelector(`#root_absolute_q .error-comment-accumulate[data-questionid="${questionId}"]`);

            let checkIfPositiveNum = await self.obsItem.callMethodReturn({
                value,
                zero: false
            }, 'isIntPositiveNum');

            // Error
            if (!checkIfPositiveNum || +value >+maxPoint) {
                saveBtn.setAttribute('disabled', true);

                errEl.style.display = "inline-block";
                errEl.innerHTML = `${self.state.translate.err_integer_number} ${maxPoint}`;
            } else {
                saveBtn.removeAttribute('disabled');
                errEl.style.display = "none";
            }
        }

        actions() {
            let self = this;
            this.obsItem.on('#root_absolute_q', 'click', '.manage-comments', async function() {
                let questions = document.querySelectorAll('#root_absolute_q #tableBlock tbody');
                if (questions.length === 0) {
                    return;
                }

                await self.manageUpdate();
                $('#root_absolute_q #manage_modal').modal('show');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });

            // Edit comment
            this.obsItem.on('#root_absolute_q', 'click', '.edit-comment-btn', async function() {
                let state = self.obsItem.getState();
                let elNowId = +this.getAttribute("data-id");
                let sequenceNow = +this.getAttribute("data-sequence");
                let questionId = +this.getAttribute("data-questionid");
                let maxPoint = +this.getAttribute("data-point");
                let data = self.dataObj[questionId][elNowId];

                let gradingMethod = ' ';
                if (state.method === 1){
                    gradingMethod = state.translate['accumulate_grade'];
                }
                if (state.method === 2){
                    gradingMethod = state.translate['scoring_lower'];
                }

                self.manageClearAllAtto();

                // if comment is global change state
                state.globalMaxGrade.global = data.isglobal;
                self.obsItem.setState(state);

                let blockEl = document.querySelector(`#root_absolute_q .textarea-block-${elNowId} div`);
                const attoTemplate = await Templates.render('gradingform_absquestion/editcomment', {
                    textareaid: `textarea_${elNowId}`,
                    comments: data.text,
                    points: data.points,
                    maxPoint,
                    isglobal: data.isglobal ? "checked" : "",
                    sequence: sequenceNow,
                    id: elNowId,
                    questionId,
                    gradingMethod,
                });

                blockEl.insertAdjacentHTML("beforeend", attoTemplate);

                let textareaForAtto = document.getElementById(`textarea_${elNowId}`);

                textareaForAtto.style.display = "block";

                self.obsItem.callMethodReturn(`textarea_${elNowId}`, "initEditorAtto");
            });

            // Cancel btn
            this.obsItem.on('#root_absolute_q', 'click', '.edit_comment_cancel', async function() {
                self.manageClearAllAtto();
            });

            // Save btn
            this.obsItem.on('#root_absolute_q', 'click', '.edit_comment_save', async function() {
                let parrent = this.closest("#root_absolute_q .comment");

                let textareaEl = parrent.querySelector("#root_absolute_q .editor_atto_content");

                let pointsEl = parrent.querySelector("#root_absolute_q .points-add-comment");

                let isglobalEl = parrent.querySelector("#root_absolute_q .isglobal-add-comment");

                let sequence = +this.getAttribute('data-sequence');
                let id = +this.getAttribute('data-id');

                let questionId = this.getAttribute('data-questionid');

                let setCommentRez = await self.obsItem.callMethodReturn({
                    textareaEl,
                    pointsEl,
                    isglobalEl,
                    sequence,
                    id,
                    questionId
                }, "setComments");

                if (setCommentRez) {
                    await self.manageUpdate();
                }
            });

            // Delete comment modal show
            this.obsItem.on('#root_absolute_q', 'click', '.delete-comment-btn', function() {
                self.deleteCommentId = +this.getAttribute("data-id");

                $('#root_absolute_q #choice_modal').modal('show');
            });

            // Delete comment yes
            this.obsItem.on('#root_absolute_q', 'click', '#choice_modal_yes', async function() {
                const id = self.deleteCommentId;
                if (!id) {
                    return;
                }

                const promise = await Ajax.call([
                    {methodname: 'gradingform_absquestion_delete_comments', args: {id: self.deleteCommentId}}
                ])[0].done((data) => {
                    // eslint-disable-next-line no-console
                    console.log(data);
                    self.obsItem.callMethodReturn(false, "toastSuccessShow");
                    return data;
                }).fail(function(err) {
                    // eslint-disable-next-line no-console
                    console.log(err);
                    return false;
                });

                if (promise) {
                    self.manageUpdate();
                }
            });

            // Add comment from manager popup
            this.obsItem.on('#root_absolute_q', 'click', '.add-comment-btn', async function() {
                let state = self.obsItem.getState();
                let attoTeaxareaBlocks = document.querySelectorAll('#root_absolute_q .textarea-i-block div');
                attoTeaxareaBlocks.forEach(function(el) {
                    el.innerHTML = "";
                });

                let sequence = +this.getAttribute('data-sequence');
                let questionId = this.getAttribute('data-questionid');
                let commentId = `abscomment_${questionId}`;
                let point = this.getAttribute('data-point');
                let qOrder = this.getAttribute('data-qorder');
                let subqOrder = this.getAttribute('data-subqorder');

                state.globalMaxGrade.global = false;
                self.obsItem.setState(state);

                await self.obsItem.callMethodReturn({
                    sequence,
                    commentId,
                    btnSaveId: 'save_new_comment_manag',
                    questionId,
                    point,
                    qOrder,
                    subqOrder,
                }, 'addCommentDialog');

                await self.checkAccumulateGrade({point, questionId});
            });

            // Save new comment manage popup
            this.obsItem.on('#root_absolute_q', 'click', '#save_new_comment_manag', async function() {
                let textareaEl = document.querySelector("#root_absolute_q #add_comment_modal .textarea-add-comment");

                let pointsEl = document.querySelector("#root_absolute_q #add_comment_modal .points-add-comment");

                let isglobalEl = document.querySelector("#root_absolute_q #add_comment_modal .isglobal-add-comment");

                let sequence = +this.getAttribute('data-sequence');

                let questionId = this.getAttribute('data-questionid');

                let result = await self.obsItem.callMethodReturn({
                    textareaEl,
                    pointsEl,
                    isglobalEl,
                    sequence,
                    id: 0,
                    questionId
                }, 'setComments');

                if (result) {
                    self.manageUpdate();
                }
            });

            // Points-add-comment change
            this.obsItem.on('#root_absolute_q', 'input', '.points-add-comment', async function(e) {
                let state = self.obsItem.getState();
                let questionId = e.target.dataset.questionid;
                let point;

                if (state.globalMaxGrade.global){
                    point = state.globalMaxGrade.minPoint;
                } else {
                    point = e.target.dataset.point;
                }
                await self.checkAccumulateGrade({point, questionId});
            });
        }
    };
});