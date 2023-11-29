import $ from 'jquery';
import * as Tables from 'quiz_advancedoverview/tables';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import * as Charts from 'quiz_advancedoverview/charts';

export let anonymousmodestate = 0;
export let CONFIG = {
    participants: new Proxy({}, {
        set(target, key, value) {
            target[key] = value;
            regenerateTable();
            return true;
        }
    }),
    anonymous_mode: 0
};

export const showLoadingIcon = () => {
    document.body.classList.add('petel-loading');
};

export const regenerateTable = function (callback = null) {
    const cmid = $('#datacmid').data('cmid');
    const groupid = $('#datagroupid').data('groupid');
    Ajax.call([{
        methodname: 'quiz_advancedoverview_render_dynamic_block',
        args: {
            cmid: cmid,
            groupid: groupid,
            config: JSON.stringify(CONFIG),
        },
        done: function (response) {
            const data = JSON.parse(response);
            const dataTableAccordingStudents = data.data_table_according_students;
            const dataTableStudentsSummary = data.data_table_students_summary;
            const anon = CONFIG.anonymous_mode;

            Tables.default.initstudentstable(dataTableAccordingStudents, dataTableStudentsSummary, anon);
            Charts.initcharts(data.charts);
            if (callback) {
                callback();
            }
        },
        fail: Notification.exception
    }]);
};

export const setAnonToggl = function (state) {
    if (+state == 1) {
        $('#anonymousStripe').removeClass('hidden');
        $('#anonymousStripe').attr('style', 'top:' + $('nav.navbar-petel').outerHeight() + 'px');
        let stateText = $('#anonymousmodeToggler1').closest('.advancedoverview_report-toggle').data('texton');
        $('.advancedoverview_report-toggle.anonymousmode').each(function (index) {
            $('.advancedoverview_report-toggle.anonymousmode').eq(index).find('.link-btn-text').html(stateText);
        });
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', true);
    } else {
        $('#anonymousStripe').addClass('hidden');
        let stateText = $('#anonymousmodeToggler1').closest('.advancedoverview_report-toggle').data('textoff');
        $('.advancedoverview_report-toggle.anonymousmode').each(function (index) {
            $('.advancedoverview_report-toggle.anonymousmode').eq(index).find('.link-btn-text').html(stateText);
        });
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', false);
    }
};

export const init = function () {
    // Add anonymous mode sticky stripe.
    $(function () {
        let stripeContent = `
                <div id="anonymousStripe"
                    class="sticky-stripe align-items-center justify-content-center bg-warning hidden">
                    <div class="d-flex align-items-center advancedoverview_report-toggle anonymousmode">
                        <input type="checkbox" id="anonymousmodeToggler3" class="toggle-btn">
                        <label for="anonymousmodeToggler3" class="mb-0 anonymousmodeToggler"> </label>
                        <span class="link-btn-text ml-3"></span>
                    </div>
                    <i class="fas fa-user-secret ml-4"></i>
                </div>`;

        $('nav.navbar').after(stripeContent);
        $('#studentstableNavFilter').find('.nav.nav-tabs .nav-link').first().addClass('active').attr('aria-pressed', 'true');
    });

    // Change partisipiants filter.
    $(document).on('click', '#studentstableNavFilter .nav-link', function (e) {
        let target = $(e.currentTarget);
        let name = target.data('name');
        CONFIG.participants.states = [name];
        showLoadingIcon();
    });

    // Change scores range.
    $(document).on('change', '#studentsActionsCollapse .custom-control-input[data-type="score_ranges"]', function (e) {
        let target = $(e.currentTarget).closest('.col');
        let score_ranges = [];
        let checkboxes = target.find('input:checked');
        checkboxes.each((i) => score_ranges.push(checkboxes[i].value));
        CONFIG.participants.score_ranges = score_ranges;
        showLoadingIcon();
    });

    // Change attempts range.
    $(document).on('change', '#studentsActionsCollapse .custom-control-input[data-type="attempts_range"]', function (e) {
        let target = $(e.currentTarget).closest('.col');
        let attempts_range = [];
        let checkboxes = target.find('input:checked');
        checkboxes.each((i) => attempts_range.push(checkboxes[i].value));
        CONFIG.participants.attempts_range = attempts_range;
        showLoadingIcon();
    });

    $(document).on('click', '#clearPillsArea', function () {
        CONFIG = {
            participants: new Proxy({}, {
                set(target, key, value) {
                    target[key] = value;
                    regenerateTable();
                    return true;
                }
            }),
            anonymous_mode: anonymousmodestate
        };
        regenerateTable();

    });
    // Search.
    $(document).on('keyup', '#searchinput', function () {
        let searchValue = $(this).val().trim();
        if (searchValue.length >= 1 || searchValue === '') {
            CONFIG.participants.search = searchValue;
            showLoadingIcon();
        }
    });


    // Anonymous mode.
    $(document).on('change', '#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3', function () {
        const state = $(this).prop('checked');
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', state);
        CONFIG.anonymous_mode = state ? 1 : 0;
        anonymousmodestate = state ? 1 : 0;
        setAnonToggl(state);
        regenerateTable();

    });
    $(window).scroll(function () {
        if ($(window).scrollTop() > 200 && $(window).width() > 1200) {
            $('#anonymousStripe').not('.hidden').attr('style', 'top:50px');
        } else {
            $('#anonymousStripe').not('.hidden').attr('style', 'top:82px');
        }
    });
};

