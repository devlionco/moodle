define(['jquery', 'core/ajax'],
    function ($, ajax) {
        return {
            init: function () {
                this.addEventHandlers();
            },
            addEventHandlers: function () {
                $(document).on('change', '#uploadcourseimage', (e) => {
                    var $input = $("#uploadcourseimage");
                    var courseid = $('#courseid').val();
                    img = $input.prop('files')[0];
                    filename = $input.prop('files')[0].name;
                    var imagebase64 = "";
                    var reader = new FileReader();
                    reader.onloadend = function () {
                        imagebase64 = reader.result;
                        ajax.call([{
                            methodname: 'format_flexsections_change_courseimage',
                            args: {
                                img: imagebase64,
                                courseid: courseid,
                                filename: filename,
                            },
                            done: (data) => {
                                data = JSON.parse(data);
                                $('#courseheaderimage').css('background-image', 'url(' + data.url + ')')
                            },
                        }]);
                    }
                    reader.readAsDataURL(img);
                });

            },
        };
    });
