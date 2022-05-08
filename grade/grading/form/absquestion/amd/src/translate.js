define(['core/str'], function(StrTranslate) {
    const helperTr = (arr, component) => {
        let result = [];
        arr.forEach((el) => {
            result.push({
                key: el,
                component: component,
            });
        });
        return result;
    };

    return async function(arr, component = 'gradingform_absquestion') {
        const translate = await StrTranslate.get_strings(
            helperTr(arr, component)
        );

        let translateObj = {};
        for (let i = 0; i < arr.length; i++) {
            translateObj[arr[i]] = translate[i];
        }

        return translateObj;
    };
});