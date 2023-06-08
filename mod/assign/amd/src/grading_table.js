define(
    [
        "jquery",
        "local_datatables/jquery.dataTables",
        "local_datatables/dataTables.bootstrap",
        "local_datatables/dataTables.scroller",
        "local_datatables/dataTables.select",
        "local_datatables/dataTables.fixedHeader",
        "local_datatables/dataTables.buttons",
        "local_datatables/dataTables.fixedColumns",
        // "local_datatables/buttons.bootstrap"
    ],
    function($) {
        return {

            init: function() {

                const defaulttype = 'asc';
                let type = '';
                let sort = '';

                let table = $('.generaltable').dataTable({

                    initComplete: function() {

                        let self = this;
                        let slash = $('th.header.c2')[0].childNodes[1].data;
                        let element = $($('th.header.c2')[0]).find('a').first();
                        let text = element[0].childNodes[0].data + slash;
                        element.text(text);
                        $('th.header.c2')[0].childNodes[1].remove();

                        $('th.header.c2').on('click', function(e) {
                            $(e.target).find('a').removeClass('custom_sorting_desc').removeClass('custom_sorting_asc');
                        })

                        $('th.header.c2').on('click', 'a', function(e) {
                            e.preventDefault();

                            switch ($(this).data('sortby')) {
                                case 'firstname':

                                    switch (sort) {
                                        case '':
                                            sort = defaulttype;
                                            break;
                                        case 'asc':
                                            sort = 'desc';
                                        break;
                                        case 'desc':
                                            sort = 'asc';
                                            break;
                                    }

                                    if (type === 'lastname') {
                                        sort = defaulttype;
                                    }

                                    type = 'firstname';

                                    break;
                                case 'lastname':

                                    switch (sort) {
                                        case '':
                                            sort = defaulttype;
                                            break;
                                        case 'asc':
                                            sort = 'desc';
                                            break;
                                        case 'desc':
                                            sort = 'asc';
                                            break;
                                    }

                                    if (type === 'firstname') {
                                        sort = defaulttype;
                                    }

                                    type = 'lastname';

                                    break;
                            }

                            $(e.target).parent().find('a').removeClass('custom_sorting_desc').removeClass('custom_sorting_asc');

                            setTimeout(function() {
                                $(e.target).parent().removeClass('sorting_desc').removeClass('sorting_asc');
                            }, 50);

                            switch (sort) {
                                case 'asc':
                                    $(e.target).removeClass('custom_sorting_desc');
                                    $(e.target).addClass('custom_sorting_asc');
                                    break;
                                case 'desc':
                                    $(e.target).removeClass('custom_sorting_asc');
                                    $(e.target).addClass('custom_sorting_desc');
                                    break;
                            }

                            self.api().order([[2, sort]]).draw();
                        });

                    },

                    // "columnDefs": [
                    //     { "type": "username", targets: 2 }
                    // ],

                    "aoColumnDefs": [{
                        "sType": "username",
                        "bSortable": true,
                        "aTargets": [2]

                        },
                        {
                        "sType": "username",
                        "bSortable": false,
                        "aTargets": [0]
                        }
                    ],

                    "aaSorting": [],
                    "iDisplayLength": 100,
                    'aLengthMenu': [[25, 100, 200, -1], [25, 100, 200, 'All']],
                    "bScrollInfinite": true,
                    // 'sPaginationType': 'full_numbers',
                    autoWidth: true,
                    paginate: false,
                    fixedHeader: {
                        header: true,
                        footer: true
                    },
                    // ScrollX: '100%',
                    fixedColumns: true,
                    // {
                    //    left: 3
                    // },
                    scrollY: 700,
                    // ScrollCollapse: true,
                    scroller: false,
                    info: false,
                    select: true,
                    dom: 'Bfrtip',
                    buttons: [],
                    'oLanguage': {
                        'sSearch': 'סינון תצוגה לפי: ',
                    }
                    /*
                    'oLanguage': {
                        'oAria': {
                            'sSortAscending': M.str.block_configurable_reports.datatables_sortascending,
                            'sSortDescending': M.str.block_configurable_reports.datatables_sortdescending,
                        },
                        'oPaginate': {
                            'sFirst': M.str.block_configurable_reports.datatables_first,
                            'sLast': M.str.block_configurable_reports.datatables_last,
                            'sNext': M.str.block_configurable_reports.datatables_next,
                            'sPrevious': M.str.block_configurable_reports.datatables_previous
                        },
                        'sEmptyTable': M.str.block_configurable_reports.datatables_emptytable,
                        'sInfo': M.str.block_configurable_reports.datatables_info,
                        'sInfoEmpty': M.str.block_configurable_reports.datatables_infoempty,
                        'sInfoFiltered': M.str.block_configurable_reports.datatables_infofiltered,
                        'sInfoThousands': M.str.langconfig.thousandssep,
                        'sLengthMenu': M.str.block_configurable_reports.datatables_lengthmenu,
                        'sLoadingRecords': M.str.block_configurable_reports.datatables_loadingrecords,
                        'sProcessing': M.str.block_configurable_reports.datatables_processing,
                        'sSearch': M.str.block_configurable_reports.datatables_search,
                        'sZeroRecords': M.str.block_configurable_reports.datatables_zerorecords
                    }
                    */
                });

                function extractName(s, type) {
                    let span = document.createElement('span');
                    span.innerHTML = s;

                    let name = span.textContent || span.innerText;
                    let arr = name.trim().split(" ");

                    let pos = -1;
                    if (type === 'firstname') {
                        pos = 0;
                    }

                    if (type === 'lastname') {
                        pos = 1;
                    }

                    if (pos >= 0 && arr[pos] !== undefined && arr[pos].length > 0) {
                        return arr[pos].trim();
                    } else {
                        return '';
                    }

                }

                $.fn.dataTableExt.oSort["username-asc"] = function(x, y) {

                    let xname = extractName(x.toString(), type);
                    let yname = extractName(y.toString(), type);

                    return xname.localeCompare(yname);
                };

                $.fn.dataTableExt.oSort["username-desc"] = function(x, y) {

                    let xname = extractName(x.toString(), type);
                    let yname = extractName(y.toString(), type);

                    return yname.localeCompare(xname);
                };

                // $.fn.dataTableExt.oSort["username-pre"] = function(num) {
                //     return true;
                // }

            }
        };
    }
);
