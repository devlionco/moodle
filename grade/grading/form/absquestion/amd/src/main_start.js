define([
    'gradingform_absquestion/observer',
    'gradingform_absquestion/table',
    'gradingform_absquestion/init_modal',
    'gradingform_absquestion/grouppas',
    'gradingform_absquestion/output',
    'gradingform_absquestion/err',
    'gradingform_absquestion/translate',
    'gradingform_absquestion/state_init',
    'gradingform_absquestion/save_modal',
    'gradingform_absquestion/toast',
    'gradingform_absquestion/comments',
    'gradingform_absquestion/manage',
    'gradingform_absquestion/info_add',
], function(
    Observer,
    Table,
    InitModal,
    GrouppasCl,
    OutputCl,
    ErrCl,
    translateFn,
    stateInitFn,
    SaveModalCl,
    ToastCl,
    CommentsCl,
    ManageCl,
    InfoAddCl
) {
    return class MainStart {
        constructor(assignid, maxgrade, qtotalmax, subqnummax, freese) {

            console.log('freese ! ', freese);

            if (MainStart._instance) {
                this.params = {
                    assignid,
                    maxgrade,
                    qtotalmax,
                    subqnummax,
                    freese,
                };
                return MainStart._instance;
            }
            this.params = {
                assignid,
                maxgrade,
                qtotalmax,
                subqnummax,
                freese,
            };
            MainStart._instance = this;
        }

        async startInitialization(reopen = false){
            let state = await stateInitFn(this.params);

            // eslint-disable-next-line no-console
            console.log('state =>', state);

            if (!state) {
                return;
            }

            // if modal window was reopen
            state.reopen = reopen;

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
                'err_grading_method',
                'info',
                'err_integer_number',
                'draft_mode',
                'ready_to_submit',
                'add_a_note',
                'total_q_groups_0',
                'scoring_lower',
                'accumulate_grade',
                'freese_warning',
            ];

            let translate = await translateFn(translateArr);
            state.translate = translate;

            let obsItem = new Observer(state);
            let initModalItem = new InitModal(obsItem);
            let tableItem = new Table(obsItem);
            let grouppasItem = new GrouppasCl(obsItem);
            let outputItem = new OutputCl(obsItem);
            let errItem = new ErrCl(obsItem);
            let saveModalItem = new SaveModalCl(obsItem);
            let toastItem = new ToastCl(obsItem);
            let commentsItem = new CommentsCl(obsItem);
            let manageItem = new ManageCl(obsItem);
            let infoAddItem = new InfoAddCl(obsItem);

            obsItem.addClass(tableItem);
            obsItem.addClass(grouppasItem);
            obsItem.addClass(outputItem);
            obsItem.addClass(errItem);
            obsItem.addClass(saveModalItem);
            obsItem.addClass(toastItem);
            obsItem.addClass(commentsItem);
            obsItem.addClass(manageItem);
            obsItem.addClass(this);
            obsItem.addClass(infoAddItem);

            await initModalItem.init(state);
            await tableItem.init(state);
            await outputItem.init(state);
        }
    };
});