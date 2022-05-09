define(['core/ajax', 'core/log',], function(Ajax, log) {
    return class SaveModal {
        constructor(obsItem) {
            if (SaveModal._instance) {
                return SaveModal._instance;
            }
            SaveModal._instance = this;
            this.obsItem = obsItem;
            this.state = obsItem.getState();
        }

        getData() {
            let self = this;
            let state = this.obsItem.getState();
            let questions = [];
            let questionsEls = document.querySelectorAll('#root_absolute_q #tableBlock tbody');

            questionsEls.forEach(function(el, index) {
                const id = state.questions[index] && state.questions[index].id ? state.questions[index].id : 0;
                const sequence = index + 1;
                const qmaxPoint = +el.querySelector('.max-point-input').value;

                let qmax;
                if (self.obsItem.callMethodReturn({value: qmaxPoint}, 'isIntPositiveNum')){
                    qmax = qmaxPoint;
                } else {
                    qmax = 0;
                }

                const group = +el.querySelector('.groups-select').value;
                const bonus = +el.querySelector('.bonus-checkbox').checked;

                const allSubqMax = el.querySelectorAll('.sub-points-input');
                let subq = [];
                allSubqMax.forEach(function(elIn, indexIn) {
                    const stateQ = state.questions;
                    let subQmax;
                    if (self.obsItem.callMethodReturn({value:  +elIn.value}, 'isIntPositiveNum')){
                        subQmax = +elIn.value;
                    } else {
                        subQmax = 0;
                    }

                    subq.push({
                        id: stateQ[index] && stateQ[index].subq &&
                        stateQ[index].subq[indexIn] && stateQ[index].subq[indexIn].id ? stateQ[index].subq[indexIn].id : 0,
                        sequence: indexIn + 1,
                        qmax: subQmax
                    });
                });

                questions.push({
                    id,
                    sequence,
                    qmax,
                    group,
                    bonus,
                    subq,
                });
            });

            let groups = [];
            const totalQGroupsIndex = +document.querySelector(`#total_questions_groups`).value;

            for (let i = 0; i < totalQGroupsIndex; i++) {
                groups.push({
                    id: state.groups[i] && state.groups[i].id ? state.groups[i].id : 0,
                    sequence: i + 1,
                    value: state.initGroupsArr[i + 1].value
                });
            }

            let grouppass = [];
            for (let key in state.grouppas) {
                grouppass.push({
                    sequence: key,
                    value: state.grouppas[key].current
                });
            }

            return {
                id: state.id ? state.id : 0,
                assignid: +state.assignid,
                totalmaxgrade: +state.maxgrade,
                qtotal: state.qtotalmax,
                totalqbonus: state.output && state.output.totalqbonus ? state.output.totalqbonus : 0,
                method: state.method ? state.method : 0,
                validated: state.validated ? state.validated : 0,
                questions,
                groups,
                grouppass,
            };
        }

        async saveAndContinues(validated) {
            let state = this.obsItem.getState();
            state.validated = validated;
            this.obsItem.setState(state);

            let obj = this.getData();

            // eslint-disable-next-line no-console
            console.log('obj=>', JSON.stringify(obj));

            const promise = Ajax.call([
                {methodname: 'gradingform_absquestion_save_settings', args: obj}
            ])[0].done((data) => {
                return data;
            }).fail(function(err) {
                log.error(err);
                return false;
            });

            return await promise;
        }

    };
});