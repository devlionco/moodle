define(['core/ajax','core/log'], function(Ajax, log) {
    return async function(params) {
        let assignid = params.assignid;
        let maxgrade = params.maxgrade;
        let qtotalmax = +params.qtotalmax;
        let subqnummax = +params.subqnummax;
        let freese = params.freese;

        const promise = Ajax.call([
            {methodname: 'gradingform_absquestion_get_settings', args: {assignid: assignid}}
        ])[0].done((data) => {
            return data;
        }).fail(function(err) {
            log.error(err);
            return false;
        });

        let stateIn = await promise;

        if (!stateIn) {
            return false;
        }

        // console.log(JSON.stringify(stateIn));

        if (stateIn.length === 0) {
            stateIn = {
                "id": 0,
                "assignid": assignid,
                "totalmaxgrade": maxgrade,
                "qtotal": 10,
                "totalqbonus": 0,
                "method": 0,
                "validated": 0,
                "questions": [],
                "groups": [],
                "grouppass": [],
            };
        }

        let state = {};

        state.id = stateIn.id;
        state.assignid = stateIn.assignid;
        state.maxgrade = maxgrade;
        state.totalmaxgrade = stateIn.totalmaxgrade;
        state.qtotalmax = qtotalmax > 0 ? qtotalmax : 10;
        state.validated = stateIn.validated;
        state.method = stateIn.method;
        state.freese = freese;

        state.questions = [];

        // if (stateIn.questions.length > 10){
        //     stateIn.questions.length = 10;
        // }

        stateIn.questions.forEach((el) => {

            let subq = [];
            if (el.subq && el.subq.length > 0) {
                el.subq.forEach((obj) => {
                    subq.push({
                        id: obj.id,
                        maxpoint: obj.qmax,
                        sequence: obj.sequence,
                        info: obj.info ? obj.info : '',
                    });
                });
            }

            state.questions.push({
                id: el.id,
                sequence: el.sequence,
                maxpoints: el.qmax,
                bonus: !!el.bonus,
                group: el.group,
                subq,
                subqLPast: subq.length,
                info: el.info ? el.info : '',
            });
        });

        state.questionsLPast = stateIn.questions.length;
        state.questionCurrent = 0;
        state.subqnummax = [...Array(subqnummax && subqnummax > 0 ? subqnummax + 1 : 11).keys()];

        state.groupsCurrent = stateIn.groups.length;
        state.groups = stateIn.groups;
        state.initGroupsArr = [
            {
                id: 0,
                value: ''
            },
            {
                id: 1,
                value: 'A'
            },
            {
                id: 2,
                value: 'B'
            },
            {
                id: 3,
                value: 'C'
            },
            {
                id: 4,
                value: 'D'
            },
            {
                id: 5,
                value: 'E'
            },
            {
                id: 6,
                value: 'F'
            },
            {
                id: 7,
                value: 'G'
            },
            {
                id: 8,
                value: 'H'
            },
            {
                id: 9,
                value: 'I'
            },
            {
                id: 10,
                value: 'J'
            },
        ];

        let grouppas = {};

        if (stateIn.grouppass && stateIn.grouppass.length > 0) {
            stateIn.grouppass.forEach((el) => {
                grouppas[el.sequence] = {
                    current: el.value
                };
            });
        }

        state.grouppas = grouppas;

        state.output = {};
        state.initQuestion = {
            subq: [],
            maxpoints: 1,
            bonus: false,
            subqLPast: 0,
            group: 0,
            info: '',
        };
        state.totalQuestions = state.questions.length;
        state.direction = document.querySelector('html').getAttribute('dir') === 'ltr' ? true : false;

        return state;
    };
});