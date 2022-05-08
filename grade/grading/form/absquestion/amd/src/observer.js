define(['core/templates'], function(Templates) {
  return class Observer {
    constructor(state) {
      this.state = state;
      this.observers = [];
    }

    getState() {
      return this.state;
    }

    setState(state) {
      this.state = state;
    }

    addClass(classEl) {
      this.observers.push(classEl);
    }

    removeClass(classEl) {
      this.observers = this.observers.filter(subscriber => subscriber !== classEl);
    }

    changeDom(state) {
      this.state = state;
      this.observers.forEach(function(classEl) {
        classEl.change(state); // Call change method
      });
      // console.log('state =>', state);
    }

    callMethodReturn(el, method) {
      let result = false;
      this.observers.forEach(function(classEl) {
        if (classEl && classEl[method]) {
          result = classEl[method](el);
        }
      });
      return result;
    }

    // Call any methods
    callMethod(state, method) {
      this.state = state;
      this.observers.forEach(function(classEl) {
        if (classEl && classEl[method]) {
          classEl[method](state); // Call change method
        }
      });
      // console.log('state =>', state);
    }

    // ============ helpers

    loadTemplate(template, paramsObj) {
      return Templates.render(template, paramsObj);
    }

    // Action after innerhtml
    on(elSelector, eventName, selector, fn) {
      var element = document.querySelector(elSelector);

      element.addEventListener(eventName, function(event) {

        var possibleTargets = element.querySelectorAll(selector);
        var target = event.target;

        for (var i = 0, l = possibleTargets.length; i < l; i++) {
          var el = target;
          var p = possibleTargets[i];

          while (el && el !== element) {
            if (el === p) {
              return fn.call(p, event);
            }

            el = el.parentNode;
          }
        }
      });
    }

    // Add objects to array and crop array
    pushItemsToArr(lengthTo, pushArr, pushObj) {
      let result = [...pushArr];
      for (let i = 0; i < lengthTo; i++) {
        if (!result[i]) {
          // PushObj.id = i;
          pushObj.idText = i + 1;
          pushObj.sequence = i + 1;
          result.push({...pushObj});
        }
      }

      result.length = lengthTo;

      return result;
    }
  };
});