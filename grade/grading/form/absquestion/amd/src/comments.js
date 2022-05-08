define([
    'jquery',
    'core/yui',
    'core/ajax',
    'core/str',
    'core/notification',
    'core/modal_factory',
    'core/modal_events',
    'core/templates',
    'gradingform_absquestion/translate',
    ], function($, Y, Ajax, Str, Notification, ModalFactory, ModalEvents, Templates) {

    return class Comments {
        constructor(obsItem) {
            if (Comments._instance) {
                this.obsItem = obsItem;
                this.state = this.obsItem.getState();
                return Comments._instance;
            }
            Comments._instance = this;
            this.obsItem = obsItem;
            this.state = this.obsItem.getState();
            this.commentsid = 0; // Textarea id
            this.actions();
        }

        async addCommentDialog(obj) {
            let state = this.obsItem.getState();
            let sequence = obj.sequence;
            let commentId = obj.commentId;
            let btnSaveId = obj.btnSaveId;
            let questionId = obj.questionId;
            let point = obj.point;
            let qOrder = obj.qOrder;
            let subqOrder = obj.subqOrder;

            let gradingMethod = ' ';
            if (state.method === 1){
                gradingMethod = state.translate['accumulate_grade'];
            }
            if (state.method === 2){
                gradingMethod = state.translate['scoring_lower'];
            }

            // Modal Add
            const modalTemplate = await Templates.render('gradingform_absquestion/addcommentmodal', {
                sequence,
                btnSaveId,
                questionId,
                qOrder,
                subqOrder
            });

            const addModalEl = document.querySelector("#root_absolute_q #add_comment_block");

            addModalEl.innerHTML = modalTemplate;

            // Textarea and fields inside modal 'Add'
            const body = await Templates.render('gradingform_absquestion/addcomment', {
                textareaid: commentId,
                point,
                questionId,
                gradingMethod,
            });

            const bodyEl = document.querySelector("#root_absolute_q #add-comment-body");

            bodyEl.innerHTML = body;

            // Textarea Atto init
            await this.initEditorAtto(commentId);

            $("#root_absolute_q #add_comment_modal").modal('show');
        }

        actions() {
            const self = this;
            this.obsItem.on('#root_absolute_q', 'click', '.add-comments', async function(e) {
                e.preventDefault();
                let state = self.obsItem.getState();
                let sequence = e.target.dataset.sequence;
                let questionId = e.target.dataset.questionid;
                let commentId = `abscomments_${questionId}`;
                let point = e.target.dataset.point;
                let qOrder = e.target.dataset.qorder;
                let subqOrder = e.target.dataset.subqorder;

                state.globalMaxGrade.global = false;
                self.obsItem.setState(state);

                await self.addCommentDialog({
                    sequence: +sequence,
                    commentId,
                    btnSaveId: 'save_new_comment',
                    questionId,
                    point,
                    qOrder,
                    subqOrder
                });

                await self.obsItem.callMethodReturn({point, questionId}, 'checkAccumulateGrade');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });

            // Comment save
            this.obsItem.on('#root_absolute_q', 'click', '#save_new_comment', async function() {
                let textareaEl = document.querySelector("#root_absolute_q #add_comment_modal .textarea-add-comment");

                let pointsEl = document.querySelector("#root_absolute_q #add_comment_modal .points-add-comment");

                let isglobalEl = document.querySelector("#root_absolute_q #add_comment_modal .isglobal-add-comment");

                let sequence = +this.getAttribute('data-sequence');
                let questionId = this.getAttribute('data-questionid');

                self.setComments({textareaEl, pointsEl, isglobalEl, sequence, id: 0, questionId});
            });

            // if comment is global
            this.obsItem.on('#root_absolute_q', 'click', '.isglobal-add-comment', async function(e) {
                let state = self.obsItem.getState();
                const questionId = e.target.dataset.questionid;
                let point;
                if (e.target.checked){
                    point = state.globalMaxGrade.minPoint;
                    state.globalMaxGrade.global = true;
                } else {
                    point = e.target.dataset.point;
                    state.globalMaxGrade.global = false;
                }
                await self.obsItem.callMethodReturn({point, questionId}, 'checkAccumulateGrade');
            });

            // atto editor submenu z-index
            this.obsItem.on('body', 'click', '.editor_atto_toolbar button', function() {
                $("body .moodle-dialogue-base .moodle-dialogue").each((index, value) => {
                    let self = $(value);
                    let zIndex = self.css("z-index");

                    if (+zIndex < 3000){
                        self.css("z-index", "3000");
                    }
                });
            });
        }

        // Parameters {textareaEl, pointsEl, isglobalEl, sequence: self.sequence, id: 0}
        async setComments(initObj) {
            let self = this;
            let state = self.obsItem.getState();


            let data = {};
            data.id = initObj.id; // New comment
            data.assignid = +state.assignid;
            data.qid = +initObj.questionId;
            data.text = initObj.textareaEl.innerHTML;
            data.method = +state.method;
            data.points = +initObj.pointsEl.value ? +initObj.pointsEl.value : 0;
            data.isglobal = initObj.isglobalEl.checked ? 1 : 0;

            const promise = Ajax.call([
                {methodname: 'gradingform_absquestion_set_comments', args: data}
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

            return await promise;
        }

        async getEditorConfig(id) {
            const data = await Ajax.call([{
                methodname: 'gradingform_absquestion_get_editor',
                args: {}
            }])[0];

            const editor = JSON.parse(data.editor);

            // console.log('editor ', editor);

            editor.params.elementid = id;
            editor.params.autosaveEnabled = false;
            editor.params.autosaveFrequency = "60";
            editor.params.language = this.state.direction ? "en" : "he";
            editor.params.directionality = this.state.direction ? "ltr" : "rtl";

            return editor;
        }

        async initEditorAtto(id) {
            const editor = await this.getEditorConfig(id);

            const restrictedModules = [
                "moodle-atto_image-button",
                "moodle-atto_media-button",
                "moodle-atto_h5p-button",
            ];

            editor.modules.map((item, index, arr) => {
                if (restrictedModules.includes(item)) {
                    delete arr[index];
                }
            });

            Y.use(editor.modules, function() {
                Y.M.editor_atto.Editor.init(editor.params);
            });
        }

    };
});