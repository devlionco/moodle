define([], function() {
    return class Output {
        constructor(obsItem) {
            if (Output._instance) {
                return Output._instance;
            }
            Output._instance = this;
            this.obsItem = obsItem;
        }

        init() {
            this.outputDraw();
        }

        async outputDraw() {
            this.obsItem.callMethodReturn(false, 'allErr');

            let state = this.obsItem.getState();

            let questions = document.querySelectorAll('#root_absolute_q #tableBlock tbody');

            let totalqbonusValue = 0;
            // Totalqbonus
            let totalqbonus = 0;
            questions.forEach(function(el) {
                const maxPoint = +el.querySelector('.max-point-input').value;
                const bonusEl = el.querySelector('.bonus-checkbox');
                if (bonusEl.checked) {
                    totalqbonus = totalqbonus + 1;
                    totalqbonusValue = totalqbonusValue + maxPoint;
                }
            });
            state.output.totalqbonusValue = totalqbonusValue;
            state.output.totalqbonus = totalqbonus;

            //
            let totalMaxWithGroups = 0;

            // Totalqgroup
            let totalqgroup = '';
            const grouppas = state.grouppas;

            for (let key in grouppas) {
                if (totalqgroup !== '') {
                    totalqgroup = totalqgroup + ', ';
                }
                // eslint-disable-next-line max-len
                totalqgroup = totalqgroup + `${state.translate.group} ${key} (${grouppas[key].current} ${state.translate.from} ${grouppas[key].num})`;

                totalMaxWithGroups = totalMaxWithGroups + (+grouppas[key].current * +grouppas[key].qmaxpoints);
            }
            state.totalqgroup = totalqgroup;

            // Totalmax without groups
            let totalmaxWithoutGroups = 0;
            questions.forEach(function(el) {
                const bonusEl = el.querySelector('.bonus-checkbox');
                const groupSelectEl = el.querySelector('.groups-select');
                const selectValue = groupSelectEl.value;
                // Const selectText = groupSelectEl.options[groupSelectEl.selectedIndex].text;

                if (bonusEl.checked) {
                    return;
                }

                const maxPoint = +el.querySelector('.max-point-input').value;

                if (+selectValue === 0) {
                    totalmaxWithoutGroups = +totalmaxWithoutGroups + maxPoint;
                }
            });

            state.output.totalmax = +totalmaxWithoutGroups + totalMaxWithGroups;
            if (isNaN(state.output.totalmax)) {
                state.output.totalmax = 0;
            }

            // Refresh template
            const template = await this.obsItem.loadTemplate('gradingform_absquestion/output', state);

            const outputEl = document.querySelector('#root_absolute_q #output');
            outputEl.innerHTML = template;

            this.obsItem.setState(state);
        }
    };
});