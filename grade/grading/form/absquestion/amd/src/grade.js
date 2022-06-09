require([
    'jquery',
    'core/ajax',
    'core/templates',
    'gradingform_absquestion/translate',
    'gradingform_absquestion/observer',
    'gradingform_absquestion/manage',
    'gradingform_absquestion/comments',
], function(
    $,
    Ajax,
    Templates,
    translateFn,
    Observer,
    ) {
    async function start() {
        let rootabsoluteEl = document.querySelector("#root_absolute_q");
        if (!rootabsoluteEl) {
            return;
        }

        let settings = rootabsoluteEl.getAttribute('data-settings');
        if (!settings) {
            return;
        }

        settings = JSON.parse(settings);
        settings = {
            assignid: settings[0],
            decorateQuestions: settings[4],
            gradingMethod: settings[5],
        };

        const translateArr = [
            'question',
            'questions',
            'n_of_sub_questions',
            'branch',
            'points',
            'group',
            'from',
            'err_sub_sum',
            'err_integer',
            'error',
            'err_total_max_grade',
            'add',
            'manage_comments',
            'number_of_subq_0',
            'number_of_subq',
            'must_be_number',
            'grading_method_0',
            'err_integer_number',
            'scoring_lower',
            'accumulate_grade',
        ];

        let state = {};
        let translate = await translateFn(translateArr);
        state.translate = translate;
        state.direction = document.querySelector('html').getAttribute('dir') === 'ltr' ? true : false;
        state.method = 2; // TEMP

        state = {...state, ...settings};

        //==== add data-globalpoint
        let minPoint = Number.POSITIVE_INFINITY;
        let pointsEls = document.querySelectorAll("#root_absolute_q .add-comment-btn");

        pointsEls.forEach((el) => {
            const point = el.getAttribute('data-max');
            if (minPoint > +point){
                minPoint = +point;
            }
        });

        state.globalMaxGrade = {
            minPoint,
            global: false
        };
        // ====

        let obsItem = new Observer(state);

        obsItem.on('body', 'click', '#root_absolute_q .add-comment-btn', async function() {
            let sequence = +this.getAttribute('data-sequence');
            let questionId = this.getAttribute('data-questionid');
            let commentId = `abscomment_${questionId}`;
            let point = +this.getAttribute('data-max');
            let qOrder = this.getAttribute('data-qorder');
            let subqOrder = this.getAttribute('data-subqorder');

            await addCommentDialog({
                sequence,
                commentId,
                btnSaveId: 'save_new_comment_manag',
                questionId,
                point,
                qOrder,
                subqOrder,
            });

            await checkAccumulateGrade({point, questionId});
        });

        obsItem.on('body', 'input', '#root_absolute_q .points-add-comment', async function(e) {
            let point = e.target.dataset.point;
            let questionId = e.target.dataset.questionid;

            await checkAccumulateGrade({point, questionId});
        });

        // if comment is global
        obsItem.on('#root_absolute_q', 'click', '.isglobal-add-comment', async function(e) {
            const questionId = e.target.dataset.questionid;
            let point;
            if (e.target.checked){
                point = state.globalMaxGrade.minPoint;
                state.globalMaxGrade.global = true;
            } else {
                point = e.target.dataset.point;
                state.globalMaxGrade.global = false;
            }
            await checkAccumulateGrade({point, questionId});
        });

        obsItem.on('body', 'click', '#root_absolute_q #save_new_comment_manag', async function() {
            let textareaEl = document.querySelector("#root_absolute_q #add_comment_modal .textarea-add-comment");

            let pointsEl = document.querySelector("#root_absolute_q #add_comment_modal .points-add-comment");

            let isglobalEl = document.querySelector("#root_absolute_q #add_comment_modal .isglobal-add-comment");

            let sequence = +this.getAttribute('data-sequence');

            let questionId = this.getAttribute('data-questionid');

            let result = await setComments({
                textareaEl,
                pointsEl,
                isglobalEl,
                sequence,
                id: 0,
                questionId
            });

            if (result) {
                // update right sidebar
                refreshComments();
            }
        });

        $(document).on('click', '#root_absolute_q .grade-move-btn', function() {
            let self = $(this);
            let commentParrent = self.parent().closest('.comment');
            let dataTextEl = commentParrent.find('.strip-html');
            let dataTextElValue = dataTextEl.html();

            let editPdfCommentBtn = $("body#page-mod-assign-grader .absqeditorbutton");
            editPdfCommentBtn.attr('data-id', self.attr("data-id"));
            editPdfCommentBtn.attr('data-point', self.attr("data-point"));
            editPdfCommentBtn.attr('data-text', dataTextElValue);
            editPdfCommentBtn.attr('data-questionid', self.attr("data-questionid"));

            if (+state.decorateQuestions){
                editPdfCommentBtn.attr('data-sequence', self.attr("data-sequence"));
            } else {
                editPdfCommentBtn.attr('data-sequence', "0");
            }

            editPdfCommentBtn.attr('data-points', self.attr("data-points"));
            // editPdfCommentBtn.innerHTML =

            $("body#page-mod-assign-grader .absqeditorbutton").trigger("click");
        });

        // atto editor submenu z-index
        obsItem.on('body', 'click', '.editor_atto_toolbar button', function() {
            $("body .moodle-dialogue-base .moodle-dialogue").each((index, value) => {
                let self = $(value);
                let zIndex = self.css("z-index");

                if (+zIndex < 3000){
                    self.css("z-index", "3000");
                }
            });
        });

        async function addCommentDialog(obj) {
            let sequence = obj.sequence;
            let commentId = obj.commentId;
            let btnSaveId = obj.btnSaveId;
            let questionId = obj.questionId;
            let point = obj.point;
            let qOrder = obj.qOrder;
            let subqOrder = obj.subqOrder;

            let gradingMethod = ' ';
            if (state.gradingMethod === '1'){
                gradingMethod = state.translate['accumulate_grade'];
            }
            if (state.gradingMethod === '2'){
                gradingMethod = state.translate['scoring_lower'];
            }

            // Modal Add
            const modalTemplate = await Templates.render('gradingform_absquestion/addcommentmodal', {
                sequence,
                btnSaveId,
                questionId,
                qOrder,
                subqOrder,
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
            await initEditorAtto(commentId);

            $("#root_absolute_q #add_comment_modal").modal('show');
        }

        async function initEditorAtto(id) {
            const editor = await getEditorConfig(id);

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

        async function getEditorConfig(id) {
            const data = await Ajax.call([{
                methodname: 'gradingform_absquestion_get_editor',
                args: {}
            }])[0];

            const editor = JSON.parse(data.editor);

            editor.params.elementid = id;
            editor.params.autosaveEnabled = false;
            editor.params.autosaveFrequency = "60";
            editor.params.language = state.direction ? "en" : "he";
            editor.params.directionality = state.direction ? "ltr" : "rtl";

            return editor;
        }

        async function checkAccumulateGrade(params) {
            let maxPoint = params.point;
            let questionId = params.questionId;
            let pointsInputEl = $("#root_absolute_q input.points-add-comment");

            let value = pointsInputEl.val();

            let saveBtn = document.querySelector(`#root_absolute_q .comment_disable_btn[data-questionid="${questionId}"]`);

            let errEl = document.querySelector(`#root_absolute_q .error-comment-accumulate[data-questionid="${questionId}"]`);

            let checkIfPositiveNum = await isIntPositiveNum({
                value,
                zero: false
            });

            // Error
            if (!checkIfPositiveNum || +value >+maxPoint) {
                saveBtn.setAttribute('disabled', true);

                errEl.style.display = "inline-block";
                errEl.innerHTML = `${state.translate.err_integer_number} ${maxPoint}`;
            } else {
                saveBtn.removeAttribute('disabled');
                errEl.style.display = "none";
            }
        }

        async function isIntPositiveNum(obj) {
            let value = obj.value;
            let zero = ('zero' in obj) ? obj.zero : true;

            if (!isNaN(value)) {
                value = +value;
            } else {
                return false;
            }

            if (isFinite(value) && value === parseInt(value, 10)) {
                if (zero && value >= 0) {
                    return true;
                }
                if (!zero && value > 0) {
                    return true;
                }
            }
            return false;
        }

        async function setComments(initObj) {
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
                // self.obsItem.callMethodReturn(false, "toastSuccessShow");
                return data;
            }).fail(function(err) {
                // eslint-disable-next-line no-console
                console.log(err);
                return false;
            });

            return await promise;
        }

        async function refreshComments(){
            const promise = Ajax.call([
                {methodname: 'gradingform_absquestion_get_comments_for_template', args: {
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

            const body = await Templates.render('gradingform_absquestion/gradecomments', {data});

            let el = document.querySelector("#root_absolute_q");
            el.innerHTML = body;

            $("#root_absolute_q").append("<div id='add_comment_block'></div>");

            questionColors();
        }

        function questionColors(){
            if (!+state.decorateQuestions){
                return;
            }

            let colorArr = [
                "#CFEFCF",
                "#262949",
                "#700460",
                "#A02C5D",
                "#EC0F47",
                "#EE6B3B",
                "#FBBF54",
                "#ABD96D",
                "#15C286",
                "#087353",
                "#65aee7",
            ];
            let elQ = document.querySelectorAll('body #root_absolute_q .question-block .question-title');
            elQ.forEach(function(el) {
                let order = el.getAttribute('data-colororder');
                el.style.color = colorArr[+order] ? colorArr[+order] : '';
            });
        }
        questionColors();
    }

    start();
});
