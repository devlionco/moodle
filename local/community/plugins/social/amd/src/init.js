define(['jquery', 'core/str', 'community_social/ajax', 'community_social/popup', 'community_social/render'], function ($, str, ajax, popup, render) {
    `use strict`;

    str.get_strings([
        {key: 'disablingsocialarea', component: 'community_social'},
        {key: 'choosingpubliccourses', component: 'community_social'},
        {key: 'editingtheschool', component: 'community_social'},
        {key: 'close', component: 'community_social'},
        {key: 'errormessage', component: 'community_social'},
        {key: 'cancel', component: 'community_social'},
        {key: 'excludeteacher', component: 'community_social'},
        {key: 'warning', component: 'community_social'},
        {key: 'removepeerteacher', component: 'community_social'},
        {key: 'ok', component: 'community_social'}
    ]).done(function () {
    });

    const mainBlock = document.querySelector(`#region-main .social`);

    const are_you_shure = (data, callback) => {
        ajax.data = {
            metod: 'community_social_popup_are_you_sure',
            data: JSON.stringify(data)
        };

        ajax.runPopup();

        mainBlock.addEventListener(`click`, function (event) {
            let target = event.target;

            // Handle agree.
            if (target.dataset.handler === `are_you_sure_agree`) {
                let data = $('#are_you_shure_data').val();

                $(this).find('.close_popup').attr("disabled", true);
                $(this).find('.agree_popup').attr("disabled", true);

                callback(JSON.parse(data));
                return;
            }
        })
    };

    const migrate_public_course = (userid) => {
        ajax.data = {
            metod: 'community_social_popup_migrate_public_course',
            userid: userid,
        };
        ajax.runPopup();
    };

    const get_public_course = (userid) => {
        ajax.data = {
            metod: 'community_social_popup_public_course',
            userid: userid,
        };
        ajax.runPopup();
    };

    const save_selected_pombim_courses = (userid, ids, callback) => {
        ajax.data = {
            metod: 'community_social_save_selected_pombim_courses',
            userid: userid,
            ids: JSON.stringify(ids)
        };

        ajax.run(function () {
            callback();
        });
    };

    const save_migrate_pombim_courses = (userid, data, callback) => {

        ajax.data = {
            metod: 'community_social_migrate_public_course',
            userid: userid,
        };

        data.forEach((item) => {
            ajax.data[item.name] = item.value;
        });

        ajax.run(function () {
            callback();
        });
    };

    const social_disable = () => {
        ajax.data = {
            metod: 'community_social_social_disable',
        };
        ajax.runPopup();
    };

    const school_settings = (userid) => {
        ajax.data = {
            metod: 'community_social_school_settings',
            userid: userid,
        };
        ajax.runPopup();
    };

    const school_save = (userid, value, callback) => {
        ajax.data = {
            metod: 'community_social_school_save',
            userid: userid,
            value: value
        };

        ajax.run(function () {
            callback();
        });
    };

    const request_followed_courses = (page_userid, current_userid) => {
        ajax.data = {
            metod: 'community_social_request_followed_courses',
            page_userid: page_userid,
            current_userid: current_userid,
        };
        ajax.runPopup();
    };

    const send_followed_courses = (page_userid, current_userid, ids, callback) => {
        ajax.data = {
            metod: 'community_social_send_followed_courses',
            page_userid: page_userid,
            current_userid: current_userid,
            ids: JSON.stringify(ids)
        };

        ajax.run(function () {
            callback();
        });

    };

    const remove_teacher_request = (page_userid, current_userid, courseid, userid) => {
        ajax.data = {
            metod: 'community_social_remove_teacher_request',
            courseid: courseid,
            userid: userid,
        };
        ajax.runPopup();
    };

    const remove_teacher_from_course = (userid, courseid, callback) => {
        ajax.data = {
            metod: 'community_social_remove_teacher_from_course',
            userid: userid,
            courseid: courseid
        };

        ajax.run(function () {
            callback();
        });
    };

    const user_follower_list = (page_userid, current_userid) => {
        ajax.data = {
            metod: 'community_social_user_follower_list',
            page_userid: page_userid,
            current_userid: current_userid,
        };
        ajax.runPopup();
    };

    const user_collegues_list = (page_userid, current_userid) => {
        ajax.data = {
            metod: 'community_social_user_collegues_list',
            page_userid: page_userid,
            current_userid: current_userid,
        };
        ajax.runPopup();
    };

    const follow_teacher = (page_userid, current_userid, follow_enable, callback) => {
        ajax.data = {
            metod: 'community_social_follow_teacher',
            page_userid: page_userid,
            current_userid: current_userid,
            follow_enable: follow_enable
        };

        ajax.run(function () {
            callback();
        });
    };

    const follow_teacher_by_userid = (custom_userid, page_userid, current_userid, callback) => {
        ajax.data = {
            metod: 'community_social_change_follow_teacher_by_user',
            page_userid: page_userid,
            current_userid: current_userid,
            custom_userid: custom_userid
        };

        ajax.run(function () {
            callback();
        });
    };

    const teacher_tab = (teacher_tab, search) => {
        let target_block = `#teachers_card_block`;

        ajax.data = {
            metod: 'community_social_render_teacher_block',
            teacher_tab: teacher_tab,
            target_block: target_block,
            search: search
        };

        ajax.setHTML(initHandler);
    };

    const initHandler = () => {

        $('#search_words').focus();
        var tmpstr = $('#search_words').val();
        $('#search_words').val('');
        $('#search_words').val(tmpstr);

        // Course slide on teacher cards.
        $('.tc-coursewrap').each(function () {
            if ($(this).children().length > 2) {
                $(this).find('.tc-circlebtn').css({'opacity': 1});
            }
        });

        // Teacher tab slider.
        $('.course__teachers').each(function () {
            if ($(this).prop('scrollHeight') > $(this).prop('offsetHeight')) {
                $(this).find('.tc-circlebtn').css({'opacity': 1, 'pointer-events': 'auto'});
            } else {
                $(this).find('.tc-circlebtn').css({'opacity': 0, 'pointer-events': 'none'});
            }
        });
    };

    return {
        init: function (page_userid, current_userid) {

            // Add tooltip to page.
            mainBlock.addEventListener(`click`, function (event) {
                let target = event.target;
                while (target !== mainBlock) {

                    // Close popups.
                    if (target.classList.contains(`close_popup`) || target.classList.contains(`modal_close`)) {
                        popup.remove();
                        return;
                    }

                    // Handle migrate public course.
                    if (target.dataset.handler === `migrate_public_course`) {
                        migrate_public_course(page_userid);
                        return;
                    }

                    if (target.dataset.handler === `save-migrate-public-course`) {

                        var formData = $('#popup_migrate_courses_pombim').serializeArray();

                        are_you_shure(formData, function(data){
                            save_migrate_pombim_courses(page_userid, data, function () {
                                render.asideCoursesPombim(page_userid);
                                render.coursesPombim(page_userid, initHandler);
                                render.userData(page_userid);
                            });
                            return;
                        })
                    }

                    // Handle show public course.
                    if (target.dataset.handler === `public_course`) {
                        get_public_course(page_userid);
                        return;
                    }

                    // Handle public course send choosen course.
                    if (target.dataset.handler === `save-pombim-courses`) {
                        var ids = [];
                        var form = document.querySelector('#popup_courses_pombip');
                        var inputs = Array.from(form.querySelectorAll(`input:checked`));
                        inputs.forEach((item) => {
                            ids.push(item.value);
                        });

                        save_selected_pombim_courses(page_userid, ids, function () {
                            render.asideCoursesPombim(page_userid);
                            render.coursesPombim(page_userid, initHandler);
                            render.userData(page_userid);
                        });
                        return;
                    }

                    // Handle public course send choosen course.
                    if (target.dataset.handler === `send_followed_courses`) {
                        var ids = [];
                        var form = document.querySelector('#request_followed_courses');
                        var inputs = Array.from(form.querySelectorAll(`input:checked`));
                        inputs.forEach((item) => {
                            ids.push(item.value);
                        });

                        send_followed_courses(page_userid, current_userid, ids, function () {
                            render.userData(page_userid);
                        });

                        return;
                    }

                    // Handle public course and send single course.
                    if (target.dataset.handler === `send_followed_single_course`) {
                        var ids = [];
                        let courseid = target.dataset.courseid;
                        ids.push(courseid);

                        send_followed_courses(page_userid, current_userid, ids, function () {
                            render.coursesPombim(page_userid, initHandler);
                        });

                        return;
                    }

                    // Disable social area.
                    if (target.dataset.handler === `social_disable`) {
                        social_disable();
                        return;
                    }

                    // School settings.
                    if (target.dataset.handler === `school-settings`) {
                        school_settings(page_userid);
                        return;
                    }

                    // School save.
                    if (target.dataset.handler === `school-save`) {
                        var value = $('#school-save').val();
                        school_save(page_userid, value, function () {
                            render.userData(page_userid);
                        });
                        return;
                    }

                    // Request to followed courses.
                    if (target.dataset.handler === `request-followed-courses`) {
                        request_followed_courses(page_userid, current_userid);
                        return;
                    }

                    // Request to removing teacher from course (popup).
                    if (target.dataset.handler === `remove-teacher-request`) {
                        let courseid = target.dataset.courseid;
                        let userid = target.dataset.userid;
                        remove_teacher_request(page_userid, current_userid, courseid, userid);
                        return;
                    }
                    // Removing teacher from course.
                    if (target.dataset.handler === `remove-teacher`) {
                        let courseid = target.dataset.courseid;
                        let userid = target.dataset.userid;
                        remove_teacher_from_course(userid, courseid, function () {
                            render.coursesPombim(page_userid, initHandler);
                            render.userData(page_userid);
                        });
                        return;
                    }

                    // Request to view followers list.
                    if (target.dataset.handler === `user-follower-list`) {
                        user_follower_list(page_userid, current_userid);
                        return;
                    }

                    // Request to view followers list.
                    if (target.dataset.handler === `user-collegues-list`) {
                        user_collegues_list(page_userid, current_userid);
                        return;
                    }

                    // Follow another teacher.
                    if (target.dataset.handler === `follow`) {
                        let follow_enable = target.dataset.follow_enable;
                        follow_teacher(page_userid, current_userid, follow_enable, function () {
                            render.userData(page_userid);
                        });
                        return;
                    }

                    // Follow another teacher by user id.
                    if (target.dataset.handler === `follow-user`) {
                        let custom_userid = target.dataset.custom_userid;
                        follow_teacher_by_userid(custom_userid, page_userid, current_userid, function () {
                            render.userData(page_userid, function () {
                                user_follower_list(page_userid, current_userid);
                            });
                        });
                        return;
                    }

                    // Teacher tab + seatch tab.
                    if (target.dataset.handler === `teacher_tab` || target.dataset.handler === `search_teacher`) {
                        let tab_id = target.dataset.tab_id;
                        var search = $('#search_words').val();
                        teacher_tab(tab_id, search);
                        return;
                    }

                    // SlideIn slideOut teachers tag on the course card.
                    if (target.dataset.handler === `slideCourseCard`) {
                        $(target).toggleClass('tc-rotate');
                        var scrollHeight = $(target).hasClass('tc-rotate') ? $(target).parents('.course').prop('scrollHeight') : 195;
                        $(target).parents('.course').css({'height': scrollHeight + 'px'});
                        return;
                    }

                    // SlideIn slideOut courses on the teachers card.
                    if (target.dataset.handler === `slideTeacherCard`) {
                        $(target).toggleClass('tc-rotate');
                        var scrollHeight = $(target).hasClass('tc-rotate') ? $(target).parents('.tc').prop('scrollHeight') : 140;
                        $(target).parents('.tc').css({'height': scrollHeight + 'px'});
                        return;
                    }

                    target = target.parentNode;
                }

            });

            window.onresize = function (event) {
                initHandler();
            };

            mainBlock.addEventListener('keydown', function (event) {
                if (event.keyCode === 13 && event.target.id === `search_words`) {
                    let tab_id = event.target.nextElementSibling.dataset.tab_id;
                    let search = event.target.value;
                    teacher_tab(tab_id, search);
                }
            });

            // Close all popups by esc.
            document.addEventListener('keydown', function (event) {
                if (event.keyCode === 27) {
                    popup.remove();
                }
            });

        }
    };
});
