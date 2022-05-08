define([], function() {
    return function(qId, id, state, realSubqId = null, elSubquestion) {
        const direction = state.direction ? "right" : "left";
        return `<tr><td></td>
            <td>${state.translate.branch} ${+id + 1}</td>
            <td>${state.translate.points} 
                <input 
                    data-placement="${direction}" 
                    id="sub_points_input_${+id + 1}" 
                    type="text" 
                    ${state.freese ? "disabled" : ""}
                    class="sub-points-input tooltip-err"
                    data-subq-id="${id}" 
                    title="${state.translate.err_integer}" 
                    size="3" 
                    value="${state.questions[qId].subq[id].maxpoint}"
                >
            </td>
            <td></td>
            <td></td>
            <td>
                <button 
                        type="button" 
                        class="btn btn-primary add-info ${realSubqId ? '' : 'd-none'}" 
                        data-sequence = "${+id + 1}" 
                        data-questionid="${realSubqId}"
                        data-qorder="${+qId + 1}"
                        data-subqorder="${+id + 1}"
                        ${state.freese ? "disabled" : ""}
                >
                    ${state.translate.info}
                </button>
            </td>
            <td>
                <button 
                        type="button" 
                        class="btn btn-primary add-comments ${realSubqId ? '' : 'd-none'}" 
                        data-sequence = "${+id + 1}" 
                        data-questionid="${realSubqId}"
                        data-point="${elSubquestion && elSubquestion.maxpoint ? elSubquestion.maxpoint : ''}"
                        data-qorder="${+qId + 1}"
                        data-subqorder="${+id + 1}"
                >
                    ${state.translate.add_a_note}
                </button>
            </td>
            </tr>`;
    };
});