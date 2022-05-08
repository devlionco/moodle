define([
    'core/templates',
    'gradingform_absquestion/main_start',
], function(
    Templates,
    MainStartCl,
) {
    'use strict';

    return {
        init: function(selector, assignid, maxgrade, qtotalmax, subqnummax, freese=false) {

            async function btnReady() {
                let btnTemplate = await Templates.render('gradingform_absquestion/button', {}).then((result) => {
                    return result;
                }).catch((err) => {
                    // eslint-disable-next-line no-console
                    console.log(err);
                    return false;
                });

                if (btnTemplate) {
                    let el = document.querySelector("body" + selector);
                    if (el) {
                        // el.innerHTML = btnTemplate;
                        el.insertAdjacentHTML("beforeend", btnTemplate);
                        let btnEl = document.querySelector('body' + selector + ' #pop_btn');
                        if (btnEl) {
                            btnEl.addEventListener("click", function() {
                                let mainItem = new MainStartCl(assignid, maxgrade, qtotalmax, subqnummax, freese);
                                mainItem.startInitialization();
                            });
                        }
                    }
                }
            }
            btnReady();
        },
    };
});
