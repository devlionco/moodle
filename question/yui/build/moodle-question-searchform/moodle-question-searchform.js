YUI.add('moodle-question-searchform', function (Y, NAME) {


    var SELECTORS = {
            OPTIONS: '.searchoptions'
        },
        NS;

    M.question = M.question || {};
    NS = M.question.searchform = {};

    NS.init = function() {
        var nTimer = setInterval(function() {
          if (window.jQuery) {
            $('body').on('select2:select', SELECTORS.OPTIONS, function(e) {
                $(e.target).parents('form').submit();
            });
            clearInterval(nTimer);
          }
        }, 100);
        Y.delegate('change', this.option_changed, Y.config.doc, SELECTORS.OPTIONS, this);
    };

    NS.option_changed = function(e) {
            e.target.getDOMNode().form.submit();
    };



}, '@VERSION@', {"requires": ["base", "node"]});
