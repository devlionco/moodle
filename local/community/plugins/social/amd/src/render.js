define(['core/yui', 'community_social/popup', 'community_social/loadingSpinner', 'core/ajax'], function (Y, popup, loading, Ajax) {
    `use strict`;

    const social = document.querySelector(`#region-main .social`);

    let render = {

        url: '/local/community/plugins/social/ajax/ajax.php',

        data: {},

        sesskey: M.cfg.sesskey,

        // Block Aside User Data.
        userData: function (userid) {
            loading.show();
            const userData = social.querySelector(`.user`);
            this.data.userid = userid;
            delete this.data.metod;

            Ajax.call([{
                methodname: 'community_social_render_block_user_data',
                args: this.data,
                done: function (response) {
                    popup.remove();
                    userData.innerHTML = response.content;
                    loading.remove();
                },
                fail: function (response) {
                    popup.error(response);
                    loading.remove();
                },
            }]);

        },

        // Block Aside Courses Pombim.
        asideCoursesPombim: function (userid) {
            loading.show();
            const coursesPombim = social.querySelector(`.public-course`);
            this.data.userid = userid;

            Ajax.call([{
                methodname: 'community_social_render_block_aside_courses_pombim',
                args: this.data,
                done: function (response) {
                    popup.remove();
                    coursesPombim.innerHTML = response.content;
                    loading.remove();
                },
                fail: function (response) {
                    popup.error(response);
                    loading.remove();
                },
            }]);


        },

        // Block Courses Pombim.
        coursesPombim: function (userid, callback) {
            loading.show();
            const coursesPombim = social.querySelector(`.course__wrapper`);
            this.data.userid = userid;

            Ajax.call([{
                methodname: 'community_social_render_block_courses_pombim',
                args: this.data,
                done: function (response) {
                    popup.remove();

                    if(coursesPombim != undefined){
                        coursesPombim.innerHTML = response.content;
                    }

                    loading.remove();
                    callback();
                },
                fail: function (response) {
                    popup.error(response);
                    loading.remove();
                },
            }]);

        },

        // Subjects Oercatalog.
        subjectsOercatalog: function (userid) {
            loading.show();
            const subjectsOercatalog = social.querySelector(`.subj__wrap`);
            this.data.userid = userid;

            Ajax.call([{
                methodname: 'community_social_render_block_subjects_oercatalog',
                args: this.data,
                done: function (response) {
                    popup.remove();
                    subjectsOercatalog.innerHTML = response.content;
                    loading.remove();
                },
                fail: function (response) {
                    popup.error(response);
                    loading.remove();
                },
            }]);
        }

    };

    return render;

});
