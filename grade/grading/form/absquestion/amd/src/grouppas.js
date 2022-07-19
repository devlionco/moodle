define(['gradingform_absquestion/grouppas_template'], function(GrouppasFn) {
    return class ClassGrouppas {
        constructor(obsItem) {
            if (ClassGrouppas._instance) {
                return ClassGrouppas._instance;
            }
            ClassGrouppas._instance = this;
            this.obsItem = obsItem;
            this.actions();
        }

        grouppasDraw(state) {
            // Draw grouppas
            const grouppasEl = document.querySelector('#root_absolute_q #grouppas');
            grouppasEl.innerHTML = GrouppasFn(state);
        }

        changeGrouppasByDom(state) {
            let self = this;
            let questions = document.querySelectorAll('#root_absolute_q #tableBlock tbody');

            let grouppas = {};
            questions.forEach(function(el) {
                const bonusEl = el.querySelector('.bonus-checkbox');
                if (bonusEl.checked) {
                    return;
                }

                const qmaxpoints = +el.querySelector('.max-point-input').value;
                const groupSelectEl = el.querySelector('.groups-select');
                const group = groupSelectEl.options[groupSelectEl.selectedIndex].text;

                if (group.length === 0) {
                    return;
                }

                if (!grouppas[group]) {
                    grouppas[group] = {num: 1};
                    grouppas[group].current = 1;
                    grouppas[group].qmaxpoints = qmaxpoints;
                } else {
                    grouppas[group].num = +grouppas[group].num + 1;
                }
            });

            // If state.grouppas has current value then save it
            if (Object.keys(state.grouppas).length !== 0) {
                for (let key in grouppas) {
                    if (!grouppas[key] || !state.grouppas[key]) {
                        continue;
                    }

                    if ('current' in state.grouppas[key] && +state.grouppas[key].current !== +grouppas[key].current) {
                        grouppas[key].current = +state.grouppas[key].current;
                    }
                }
            }

            // remove groups if there are less than two
            let grouppasMore = {};
            for (let key in grouppas) {
                if (grouppas[key].num >= 2) {
                    grouppasMore[key] = grouppas[key];
                }
            }

            state.grouppas = {...grouppasMore};
            self.obsItem.setState(state);
            self.grouppasDraw(state);
        }

        actions() {
            let self = this;
            this.obsItem.on('#root_absolute_q', 'change', '.select-grouppas', function() {
                let state = self.obsItem.getState();
                const value = this.value;
                const letter = this.getAttribute('data-letter');

                state.grouppas[letter].current = value;
                self.obsItem.setState(state);

                self.obsItem.callMethod(state, 'outputDraw');

                // Check errors
                self.obsItem.callMethodReturn(false, 'allErr');

                self.obsItem.callMethodReturn(false, 'changeDraftMode');
            });
        }
    };
});