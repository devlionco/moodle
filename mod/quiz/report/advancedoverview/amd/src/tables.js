/* eslint-disable complexity */
/* eslint-disable no-unused-vars */
/* eslint-disable no-undef */
import Notification from 'core/notification';
import * as Str from 'core/str';
import $ from 'jquery';
import Tabulator from 'report/advancedoverview/js/tabulator.min.js';
import * as Main from 'quiz_advancedoverview/main';
import * as studentsTableActions from 'quiz_advancedoverview/studentsTableActions';

// TODO: refactore code!
export let QLENGTH = 0;
const SELECTORS = {};
export const TEMPCONFIG = {};

export const TABLES = {};
export const translatedStrings = {};
export const initquestionstable = function(data) {

  SELECTORS.studentstableNavFilter = document.getElementById('studentstableNavFilter');
  SELECTORS.studentsActionsCollapse = document.getElementById('studentsActionsCollapse');

  let textDirection = $('html').attr('dir');

  let tabledata = JSON.parse(data);
  QLENGTH = tabledata.length;

  let content = {
    data: tabledata,
    autoColumns: true,
    movableRows: false,
    maxHeight: 280,
    textDirection: textDirection,
    layout: 'fitColumns',
    autoColumnsDefinitions: function(definitions) {
      definitions.forEach((column, i) => {
        if (i === 0) {
          column.maxWidth = '50px';
        }
        if (i === 1) {
          column.headerSort = false;
        }
        if (i === 2) {
          column.maxWidth = '80px';
        }
        if (i === 3) {
          column.maxWidth = '90px';
        }
        if (i === 4) {
          column.maxWidth = '120px';
        }
        if (i === 5) {
          column.maxWidth = '160px';
        }
        if (i === 6) {
          column.maxWidth = '140px';
        }
        if (i === 1) {
          column.formatter = 'html';
          column.widthGrow = 5;
          column.hozAlign = 'start';
          column.vertAlign = 'midddle';
          // Set the custom sorter function for the column containing the description data
          // column.sorter = function (a, b, aRow, bRow, column, dir, sorterParams) {
          //   let aDesc = $(a).find('.description').text();
          //   let bDesc = $(b).find('.description').text();
          //   return aDesc.localeCompare(bDesc);
          // };
        } else {
          column.hozAlign = 'center';
          column.vertAlign = 'middle';
          column.widthGrow = 0;
        }
      });
      return definitions;
    },
    rowFormatter: function(row) {
      row.getElement().style.height = '48px';
    },
    initialSort: [
      {column: "Wrong", dir: "desc"},
      {column: "שגו", dir: "desc"},
    ]
  };

  TABLES.questionsTable = new Tabulator('#questions-table', content);
};

export const initstudentstable = function(data, anon = 0) {
  const self = this;
  const strings = [
    {
      key: 'viewingprofile',
      component: 'quiz_advancedoverview'
    },
    {
      key: 'passwordreset',
      component: 'quiz_advancedoverview',

    },
    {
      key: 'loginasthisstudent',
      component: 'quiz_advancedoverview',

    },
    {
      key: 'allcoursereport',
      component: 'quiz_advancedoverview',

    },
    {
      key: 'courseobservationreport',
      component: 'quiz_advancedoverview',

    },
    {
      key: 'sendingmessage',
      component: 'quiz_advancedoverview',

    },
    {
      key: 'recalculategrades',
      component: 'quiz_advancedoverview',

    },
    {
      key: 'fullname',
      component: 'quiz_advancedoverview',
    },
    {
      key: 'state',
      component: 'quiz_advancedoverview',
    },
    {
      key: 'attempt_number',
      component: 'quiz_advancedoverview',
    },
    {
      key: 'grade',
      component: 'quiz_advancedoverview',
    },
    {
      key: 'starttime',
      component: 'quiz_advancedoverview',
    },
    {
      key: 'endtime',
      component: 'quiz_advancedoverview',
    },
    {
      key: 'duration',
      component: 'quiz_advancedoverview',
    },
  ];

  Str.get_strings(strings).done(function(str) {
    self.translatedStrings.viewingprofile = str[0];
    self.translatedStrings.passwordreset = str[1];
    self.translatedStrings.loginasthisstudent = str[2];
    self.translatedStrings.allcoursereport = str[3];
    self.translatedStrings.courseobservationreport = str[4];
    self.translatedStrings.sendingmessage = str[5];
    self.translatedStrings.recalculategrades = str[6];

    self.translatedStrings.fullname = str[7];
    self.translatedStrings.state = str[8];
    self.translatedStrings.attempt_number = str[9];
    self.translatedStrings.grade = str[10];
    self.translatedStrings.starttime = str[11];
    self.translatedStrings.endtime = str[12];
    self.translatedStrings.duration = str[13];


    const clickMenu = [
      {
        disabled: function(component) {
          return component.getData().userprofilelink ? false : true;
        },
        label: `<span>${self.translatedStrings.viewingprofile}</span>`,
        action: function(e, row) {
          var link = row.getData().userprofilelink;
          window.open(link, "_blank");
        }
      },
      {
        disabled: function(component) {
          return component.getData().resetpasswordlink ? false : true;
        },
        label: `<span>${self.translatedStrings.passwordreset}</span>`,
        action: function(e, row) {
          var link = row.getData().resetpasswordlink;
          window.open(link, "_blank");
        }
      },
      {
        disabled: function(component) {
          return component.getData().loginaslink ? false : true;
        },
        label: `<span>${self.translatedStrings.loginasthisstudent}</span>`,
        action: function(e, row) {
          var link = row.getData().loginaslink;
          window.open(link, "_blank");
        }
      },
      {
        disabled: function(component) {
          return component.getData().completereportlink ? false : true;
        },
        label: `<span>${self.translatedStrings.allcoursereport}</span>`,
        action: function(e, row) {
          var link = row.getData().completereportlink;
          window.open(link, "_blank");
        }
      },
      {
        disabled: function(component) {
          return component.getData().outlinereportlink ? false : true;
        },
        label: `<span>${self.translatedStrings.courseobservationreport}</span>`,
        action: function(e, row) {
          var link = row.getData().outlinereportlink;
          window.open(link, "_blank");
        }
      },
      {
        separator: true,
      },
      {
        label: `<span>${self.translatedStrings.sendingmessage}</span>`,
        action: function(e, row) {
          var userid = row.getData().userid;
          Main.showMessagePopup([userid]);
        }
      },
      {
        disabled: function(component) {
          return component.getData().attemptid ? false : true;
        },
        label: `<span>${self.translatedStrings.recalculategrades}</span>`,
        action: function(e, row) {
          var rowData = row.getData();
          Main.regradaAttemtps(+Main.TEMPDATA.cmid, +Main.TEMPDATA.courseid, +Main.TEMPDATA.quizid, rowData.attemptid);
        }
      },
    ];

    let textDirection = $('html').attr('dir');

    let tabledata = JSON.parse(data);

    let content = {
      locale: true,
      data: tabledata,
      autoColumns: true,
      movableRows: false,
      textDirection: textDirection,
      pagination: false,
      paginationSize: 20,
      dataTree: true,
      dataTreeStartExpanded: true,
      layout: 'fitDataFill',
      autoColumnsDefinitions: function(definitions) {
        definitions.forEach((column, i) => {
          column.formatter = 'html';
          const fieldName = column.field;

          switch (fieldName) {
            case 'checkbox':
              column.formatter = 'rowSelection';
              column.titleFormatter = "rowSelection";
              column.titleFormatterParams = {
                rowRange: 'active',
              };
              column.headerSort = false;
              column.hozAlign = 'center';
              column.download = false;
              break;

            case 'fullname':
              column.title = self.translatedStrings.fullname;
              column.headerSort = false;
              column.cellStyle = 'border-right: none;';
              break;

            case 'state':
              column.title = self.translatedStrings.state;
              break;

            case 'attempt_number':
              column.title = self.translatedStrings.attempt_number;
              break;

            case 'grade':
              column.title = self.translatedStrings.grade;
              break;

            case 'starttime':
              column.title = self.translatedStrings.starttime;
              break;

            case 'endtime':
              column.title = self.translatedStrings.endtime;
              break;

            case 'duration':
              column.title = self.translatedStrings.duration;
              break;

            case 'usermenubtn':
              column.headerSort = false;
              column.title = "";
              column.formatter = function(cell) {
                if (cell.getValue() === false) {
                  return ""; // Return empty string if usermenubtn is false
                } else {
                  let dropdownBtn = document.createElement('button');
                  dropdownBtn.className = 'row-menu-btn btn';
                  dropdownBtn.type = 'button';
                  dropdownBtn.innerHTML = '<i class="fa fa-ellipsis-v"></i>';
                  return dropdownBtn;
                }
              };
              column.clickMenu = clickMenu;
              column.download = false;
              break;

            case 'attemptid':
            case 'user_attempt_code':
            case 'userid':
            case 'resetpasswordlink':
            case 'userprofilelink':
            case 'loginaslink':
            case 'completereportlink':
            case 'outlinereportlink':
            case 'lastname':
            case 'firstname':
            case '_children':
              column.visible = false;
              break;
          }
          if (i <= 8) {
            column.frozen = true;
          }
        });
        return definitions;
      }
    };

    // Enable pagination.
    if (tabledata.length > 20) {
      content.pagination = true;
    }

    self.TABLES.studentsTable = new Tabulator('#students-table', content);

    self.TABLES.studentsTable.on("rowSelectionChanged", function(data, rows) {
      Main.TEMPDATA.rowData = data;
      let state = data.length > 0 ? false : true;
      Main.changeStudentActionState(state);
      Main.setSelectedStudentsStr(rows.length);
    });

    $('#downloadXlsTable').off('click');
    $('#downloadXlsTable').on('click', function() {
      var tableData = self.TABLES.studentsTable.getData();

      var td2 = [];

      // Attaching children.
      tableData.forEach(function(row) {
        if (row.hasOwnProperty('_children')) {
          td2.push(row);
          row._children.forEach(function(ch) {
            td2.push(ch);
          });
          delete row._children;
        } else {
          td2.push(row);
        }
      });

      // Modifications data for output.
      td2.forEach(function(row) {
        delete row.checkbox;
        delete row.usermenubtn;
        delete row.attemptid;
        delete row.user_attempt_code;
        delete row.userid;
        delete row.resetpasswordlink;
        delete row.userprofilelink;
        delete row.loginaslink;
        delete row.completereportlink;
        delete row.outlinereportlink;

        // Numeric value from HTML.
        for (var prop in row) {
          if (row.hasOwnProperty(prop)) {
            if (/^Q \d+ \/ \d+$/.test(prop)) {
              var value = row[prop];
              var match = value.match(/<div.*>(\d+(?:\.\d+)?)<\/div>/);
              if (match) {
                var newValue = match[1];
                row[prop] = newValue;
              }
            }
          }

          if (row.hasOwnProperty(prop)) {
            if (prop == 'fullname' || prop == 'grade') {
              var value = row[prop].toString();
              var match = value.match(/<a[^>]*>([^<]*)<\/a>/i);
              if (match) {
                var newValue = match[1];
                row[prop] = newValue;
              }
            }
          }
        }

        // Changing titles.
        for (var prop in row) {
          if (row.hasOwnProperty(prop)) {
            if (row.hasOwnProperty(prop)) {
              switch (prop) {
                case 'fullname':
                  var name = self.translatedStrings.fullname;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                case 'state':
                  var name = self.translatedStrings.state;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                case 'attempt_number':
                  var name = self.translatedStrings.attempt_number;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                case 'grade':
                  var name = self.translatedStrings.grade;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                case 'starttime':
                  var name = self.translatedStrings.starttime;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                case 'endtime':
                  var name = self.translatedStrings.endtime;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                case 'duration':
                  var name = self.translatedStrings.duration;
                  row[name] = row[prop];
                  delete row[prop];
                  break;
                default:
                  var value = row[prop];
                  delete row[prop];
                  row[prop] = value;
                  break;
              }
            }
          }

        }
      });

      // Download file.
      var workbook = XLSX.utils.book_new();
      var worksheet = XLSX.utils.json_to_sheet(td2);
      XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");
      XLSX.writeFile(workbook, "table-data.xlsx");
    });

    self.TABLES.studentsTable.on("tableBuilt", function() {
      let tableData = self.TABLES.studentsTable.getSelectedData();
      Main.TEMPDATA.rowData = tableData;
      let state = tableData.length > 0 ? false : true;
      Main.changeStudentActionState(state);
      Main.setSelectedStudentsStr(tableData.length);

      function setActive(target) {
        let parent = $(target).closest('.tabulator-col-title');
        parent.find('span').removeClass('active');
        $(target).addClass('active');
      }


      $('#students-table .tabulator-sortable.tabulator-col-sorter-element').on('click', function(e) {
        let target = $(e.target);
        if (target.hasClass('fname') || target.hasClass('sname')) {
        } else {
          $('[tabulator-field="fullname"]').find('span').removeClass('active');
        }
      });


      $('[tabulator-field="fullname"]').on('click', '.fname', function(e) {
        let sort = e.target.dataset.sort;
        let resultSort = (sort === 'desc') ? 'asc' : 'desc';
        e.target.setAttribute('data-sort', resultSort);
        setActive(e.target);
        self.TABLES.studentsTable.setSort("firstname", resultSort);
      });
      $('[tabulator-field="fullname"]').on('click', '.lname', function(e) {
        let sort = e.target.dataset.sort;
        let resultSort = (sort === 'desc') ? 'asc' : 'desc';
        e.target.setAttribute('data-sort', resultSort);
        setActive(e.target);
        self.TABLES.studentsTable.setSort("lastname", resultSort);
      });
    });

    studentsTableActions.setAnonToggl(anon);
  }).fail(Notification.exception);

};