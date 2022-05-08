define([
    'jquery',
    'core/templates',
    'core/ajax',
    ], function(
        $,
        Templates,
        Ajax,
    ) {
    return class InfoAdd {
        constructor(obsItem) {
            if (InfoAdd._instance) {
                this.obsItem = obsItem;
                this.state = this.obsItem.getState();
                return InfoAdd._instance;
            }
            InfoAdd._instance = this;
            this.obsItem = obsItem;
            this.state = this.obsItem.getState();
            this.actions();
        }

        actions() {
            const self = this;
            this.obsItem.on('#root_absolute_q', 'click', '.add-info', async function(e) {
                let state = self.obsItem.getState();
                let questionId = e.target.dataset.questionid;
                let qOrder = e.target.dataset.qorder;
                let subqOrder = e.target.dataset.subqorder;

                await self.infoDialog(questionId, state, qOrder, subqOrder);

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });

            this.obsItem.on('#root_absolute_q', 'click', '.save-info-btn', async function(e) {
                let state = self.obsItem.getState();
                let questionId = e.target.dataset.questionid;
                let textareaEl = document.querySelector(`#root_absolute_q .textarea-info`);
                let info = textareaEl.innerHTML;

                const promise = Ajax.call([
                    {methodname: 'gradingform_absquestion_set_info', args: {
                        id: questionId,
                        info
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

                if (promise){
                    let newState = self.setInfo(questionId, info, state);
                    self.obsItem.setState(newState);

                    self.obsItem.callMethodReturn(false, "toastSuccessShow");
                }
            });
        }

        setInfo(questionId, info, state){
            for (let $i = 0; $i < state.questions.length; $i++){
                let el = state.questions[$i];
                if (+el.id === +questionId){
                    state.questions[$i].info = info;
                    break;
                }
                for (let $isq = 0; $isq < state.questions[$i].subq.length; $isq++){
                    let elSubq = state.questions[$i].subq[$isq];
                    if (+elSubq.id === +questionId){
                        state.questions[$i].subq[$isq].info = info;
                        $i = state.questions.length;
                        break;
                    }
                }
            }
            return state;
        }

        getInfo(questionId, state){
            for (let $i = 0; $i < state.questions.length; $i++){
                let el = state.questions[$i];
                if (+el.id === +questionId){
                    return el.info;
                }
                for (let $isq = 0; $isq < state.questions[$i].subq.length; $isq++){
                    let elSubq = state.questions[$i].subq[$isq];
                    if (+elSubq.id === +questionId){
                        return elSubq.info;
                    }
                }
            }
            return '';
        }

        async infoDialog(questionId, state, qOrder, subqOrder) {
            let value = this.getInfo(questionId, state);

            // Modal Add
            const modalTemplate = await Templates.render('gradingform_absquestion/infomodal', {
                textareaid: `info_atto_${questionId}`,
                questionId,
                value,
                qOrder,
                subqOrder
            });

            const addModalEl = document.querySelector("#root_absolute_q #add_info_block");

            addModalEl.innerHTML = modalTemplate;

            // Textarea Atto init
            await this.obsItem.callMethodReturn(`info_atto_${questionId}`, 'initEditorAtto');

            $("#root_absolute_q #info_modal").modal('show');
        }

    };
});