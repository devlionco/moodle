define(['core/templates'], function(Templates){
  class QuestionList {
    constructor(data, popupElement, adParams) {
      this.data = {...data};
      this.data.table = data.table;
      this.popupElement = popupElement;
      this.adParams = adParams;
    }

    dataChange(){
      let objectLast = [];

      for (const [mid, clustertablebymid] of Object.entries(this.data.table)) {
        let objectTable = {
          rows:[],
          clusters: [],
          name: clustertablebymid.name
        };
        let questionsIds = [];
        for (const [clusterkey, clustertable] of Object.entries(clustertablebymid.data)) {
          for (const [partOfKey, value] of Object.entries(clustertable.table)) {
            if (!questionsIds.find(el => el === partOfKey)) {
              questionsIds.push(partOfKey);
            }

            let findEl = objectTable.rows.find(obj => obj.question === partOfKey);

            if (!findEl) {
              objectTable.rows.push({
                question: partOfKey,
                qname: value.qname,
                value: [{
                  clasterId: clusterkey,
                  clustername: clustertable.clustername,
                  color: value.color,
                  prc: value.prc
                }]
              });
            } else {
              findEl.value.push({
                clasterId: clusterkey,
                clustername: clustertable.clustername,
                color: value.color,
                prc: value.prc,
              })
            }
          }

          objectTable.clusters.push({name: clustertable.clustername, prc: clustertable.avg});
        }
        objectLast.push(objectTable);
      }

      return {tabledata: objectLast};
    }

    addAction(bubbleObject){
      let self = this;
      $(`${self.popupElement} .question-list`).css('display','none');
      $(`${self.popupElement} .q-lright-icon i`).removeClass('rotate-0');

      $(document).ready(function() {
        $("html").off("click", `${self.popupElement} .question-list-button`);
        $("html").on("click", `${self.popupElement} .question-list-button`, function(){
          let popiEl = $(`${self.popupElement} .q-lright-icon i`);
          let questionListEl = $(`${self.popupElement} .question-list`);
          let qScrollTable = $(`${self.popupElement} .question-scroll-table`);

          qScrollTable.css("display","block");

          questionListEl.toggle( "slow", function() {
            afterAnimation();
            questionListEl.stop(true);
          });

          function afterAnimation(){
            popiEl.toggleClass( "rotate-0" );

            let topBlockXPosition =
            bubbleObject.documentWith - bubbleObject.topBlockWith - bubbleObject.topBlockRightSpace - $(`${self.popupElement} .question-list-block`).width() - 7;

            let windowWidth = $(window).width();

            let topBlockYPosition;
            if (windowWidth >= 1200 && windowWidth < 1400){
              topBlockYPosition = 54;
            } else {
              topBlockYPosition = 0;
            }

            if ( windowWidth >= 1200 ){
              if (popiEl.hasClass("rotate-0")){
                bubbleObject.changePositionCirclesZero(true);
                bubbleObject.changePositionAllCircles();
                bubbleObject.topBlockObj.changePosition(topBlockXPosition, topBlockYPosition);
                bubbleObject.bottomBlockItem.changePosition(topBlockXPosition);
              } else {
                bubbleObject.changePositionCirclesZero(false);
                bubbleObject.changePositionAllCircles();
                bubbleObject.topBlockObj.changePosition();
                bubbleObject.bottomBlockItem.changePosition();
              }
            }
          }

        });

        $("html").off("click", `${self.popupElement} div.question-list-title`);
        $("html").on("click", `${self.popupElement} div.question-list-title`, function(){
          let dataId = $(this).attr("data-id");
          $(`${self.popupElement} .question-scroll-table[data-select='${dataId}']`).animate({height: "toggle"}, 400);
        });
      });
    }
  }

  return function(data, popupElement, adParams) {
    let questionListItem = new QuestionList(data, popupElement, adParams);
    let listData = questionListItem.dataChange();

    listData.averagePercent = {
      mediumlevel: data.yellow,
      hightlevel: data.green,
    };

    Templates.render('local_diagnostic/list', listData).done(function(html) {
      let listEl = $(`${popupElement} .question-list-block`);

      if (listEl.css('visibility') == 'hidden'){
          listEl.css('visibility','visible');
      }
      $(`${popupElement} .question-list`).html(html);
    });
    return questionListItem;
  }

});