define([], function() {
    return function(id, state, qRealId = null, elQuestion) {
        function translateF(el, state) {
            if (+el === 0) {
                return state.translate.number_of_subq_0;
            } else {
                // Return state.translate.number_of_subq.replace('{$a}', el);
                return el;
            }
        }

        const currentQuestion = state.questions[id];

        let subQSelect = state.subqnummax.map(function(el) {
            if (currentQuestion.subq.length === el) {
                // State.translate.number_of_subq_0;
                return `<option selected="selected" value="${el}">${translateF(el, state)}</option>`;
            } else {
                return `<option value="${el}">${translateF(el, state)}</option>`;
            }
        });
        subQSelect = subQSelect.join('');

        let groupsSelect = state.initGroupsArr.map(function(el) {
            if (+el.id > state.groupsCurrent) {
                return ``;
            }
            if (+currentQuestion.group === +el.id) {
                return `<option selected="selected" value="${el.id}">${el.id === 0 ? "" : el.id}</option>`;
            } else {
                return `<option value="${el.id}">${el.id === 0 ? "" : el.id}</option>`;
            }
        });
        groupsSelect = groupsSelect.join('');

        return `<tbody id="question_${id}"><tr class="tr_question">
            <td>
                ${state.translate.question} ${+id + 1}
            </td>
            <td>
                ${state.translate.n_of_sub_questions}
                <select data-question="${id}" class="sub-questions" ${state.freese ? "disabled" : ""}>
                    ${subQSelect}
                </select>
            </td>
            <td>
                <input 
                    id="max_point_input_${+id + 1}"
                    type="text"
                    ${state.freese ? "disabled" : ""}
                    class="max-point-input tooltip-err" 
                    data-question="${id}" 
                    size="3" data-placement="top"
                    data-template="<div class='tooltip' role='tooltip'><div
                     class='arrow'></div><div class='tooltip-inner tooltip-wide'></div></div>"
                value="${currentQuestion.maxpoints}" title="">
            </td>
            <td>
                <input
                    type="checkbox" 
                    ${currentQuestion.bonus ? 'checked' : ''} 
                    class="bonus-checkbox" 
                    data-question="${id}" 
                    ${state.freese ? "disabled" : ""}
                >
            </td>
            <td>
                <select class="groups-select" data-question="${id}" ${state.freese ? "disabled" : ""}>
                    ${groupsSelect}
                </select>
            </td>
            <td>
                <button 
                    type="button" 
                    class="btn btn-primary add-info ${qRealId ? '' : 'd-none'}" 
                    data-sequence = "${+id + 1}" 
                    data-questionid="${qRealId}"
                    data-qorder="${+id + 1}"
                    data-subqorder="0"
                    ${state.freese ? "disabled" : ""}
                >${state.translate.info}</button>
            </td>
            <td>
                <button 
                    type="button" 
                    class="btn btn-primary add-comments ${qRealId ? '' : 'd-none'}" 
                    data-sequence = "${+id + 1}" 
                    data-questionid="${qRealId}"
                    data-point="${elQuestion && elQuestion.maxpoints ? elQuestion.maxpoints : ''}"
                    data-qorder="${+id + 1}"
                    data-subqorder="0"
                >${state.translate.add_a_note}</button>
            </td>
            </tr></tbody>`;
    };
});