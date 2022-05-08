define([], function() {
    return function(state) {
        let result = '';

        let keys = Object.keys(state.grouppas);
        keys.sort();

        for (let i = 0; i < keys.length; i++) {
            let key = keys[i];
            let obj = state.grouppas[key];
            const num = obj.num;
            const current = obj.current;

            if (key.length === 0) {
                continue;
            }

            const select = selectDraw(num, current, key);
            result = result + line(key, select);
        }

        return result;

        function selectDraw(lengthSelect, currentSelect, letter) {
            // eslint-disable-next-line max-len
            let dataTemplate = "<div class='tooltip' role='tooltip'><div class='arrow'></div><div class='tooltip-inner tooltip-wide'></div></div>";

            let result = `<select data-letter="${letter}" class="select-grouppas"
            data-placement="top"
            data-template="${dataTemplate}"
            title=""
            ${state.freese ? "disabled" : ""}
            >`;
            for (let i = 0; i <= lengthSelect; i++) {
                if (+currentSelect === i) {
                    result = result + `<option selected="selected" value="${i}">${i}</option>`;
                } else {
                    result = result + `<option value="${i}">${i}</option>`;
                }
            }
            result = result + '</select>';
            return result;
        }

        function line(letter, select) {
            // eslint-disable-next-line max-len
            return `<div class="grouppas-line">${state.translate.group} ${letter} ${state.translate.from} ${select} ${state.translate.questions}</div>`;
        }
    };
});