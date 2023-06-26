define(['jquery', 'core/ajax'],
    function($, ajax) {
        return {
            initCoursePage: function() {
                let self = this;

                $('.uploadsectionimage').find('.sectionid').each(function() {
                    self.changeImagePageCourse($(this).val());
                });

                // Observer on action.
                // this.observer();
            },

            initSectionPage: function() {
                $(document).on('change', '#uploadsectionimage', (e) => {
                    var $input = $("#uploadsectionimage");
                    var sectionid = $('#sectionid').val();
                    var img = $input.prop('files')[0];
                    var filename = $input.prop('files')[0].name;
                    var imagebase64 = "";
                    var reader = new FileReader();
                    reader.onloadend = function() {
                        imagebase64 = reader.result;
                        ajax.call([{
                            methodname: 'format_flexsections_change_sectionimage',
                            args: {
                                img: imagebase64,
                                sectionid: sectionid,
                                filename: filename,
                            },
                            done: (data) => {
                                data = JSON.parse(data);
                                $('.card-img').css('background-image', 'url(' + data.url + ')');
                            },
                        }]);
                    };
                    reader.readAsDataURL(img);
                });
            },

            changeImagePageCourse: function(id) {
                $(document).on('change', '#uploadsectionimage_' + id, (e) => {
                    var $input = $("#uploadsectionimage_" + id);
                    var sectionid = $('#sectionid_' + id).val();
                    var img = $input.prop('files')[0];
                    var filename = $input.prop('files')[0].name;
                    var imagebase64 = "";
                    var reader = new FileReader();
                    reader.onloadend = function() {
                        imagebase64 = reader.result;
                        ajax.call([{
                            methodname: 'format_flexsections_change_sectionimage',
                            args: {
                                img: imagebase64,
                                sectionid: sectionid,
                                filename: filename,
                            },
                            done: (data) => {
                                data = JSON.parse(data);
                                $('#section-image-' + id).css('background-image', 'url(' + data.url + ')');
                            },
                        }]);
                    };
                    reader.readAsDataURL(img);
                });

            },

            observer: function() {
                let flagmutation = 0;
                let observerNodeTargets = document.querySelectorAll('.flexsections'),
                    observerConfig = {attributes: false, childList: true, subtree: false};

                observerNodeTargets.forEach(function(target) {
                    new MutationObserver(function(type) {
                        var addednodes = type[0].addedNodes;
                        addednodes.forEach((node) => {
                            // Let elem = $(node).find('*[data-region="' + SELECTORS.shareSectionButton + '"]');
                            flagmutation = $.now();
                        });

                        var removednodes = type[0].removedNodes;
                        removednodes.forEach((node) => {
                            flagmutation = $.now();
                        });
                    }).observe(target, observerConfig);
                });


                // Rerender simpesections.
                setInterval(function() {
                    if (flagmutation > 0) {
                        flagmutation = 0;

                        // TODO
                    }
                }, 1000);
            }
        };
    });
