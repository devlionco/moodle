define([
    'jquery',
], function($) {
    return class ErrCl {
        constructor(obsItem) {
            if (ErrCl._instance) {
                this.obsItem = obsItem;
                this.state = obsItem.getState();
                return ErrCl._instance;
            }
            ErrCl._instance = this;
            this.obsItem = obsItem;
            this.state = obsItem.getState();
            this.errorsArr = [];
        }

        qmaxHasErr(el) {
            const value = +el.value;

            if (!this.isIntPositiveNum({value, zero: false}) || value > +this.state.maxgrade) {
                // Error
                el.setAttribute('data-original-title', this.state.translate.err_integer);
                $(el).tooltip({trigger: 'manual'}).tooltip('show');
                return true;
            } else {
                $(el).tooltip({trigger: 'manual'}).tooltip('hide');
                return false;
            }
        }

        subqmaxHasErr(el) {
            const value = +el.value;

            if (!this.isIntPositiveNum({value, zero: false})) {
                // Error
                $(el).tooltip({trigger: 'manual'}).tooltip('show');
                return true;
            } else {
                $(el).tooltip({trigger: 'manual'}).tooltip('hide');
                return false;
            }
        }

        errThisQuestionAndSubq(obj) {
            let self = this;
            const parrent = obj.parrent;
            const qmax = obj.this;
            const allSubqMax = parrent.querySelectorAll('.sub-points-input');
            let result = false;

            let subqSum = 0;
            allSubqMax.forEach(function(sub) {
                if (self.subqmaxHasErr(sub)) {
                    result = true;
                }
                subqSum = subqSum + +sub.value;
            });

            if (+subqSum !== +qmax.value && +subqSum !== 0) {
                qmax.setAttribute('data-original-title', self.state.translate.err_sub_sum);
                $(qmax).tooltip({trigger: 'manual'}).tooltip('show');
                result = true;
            } else {
                if (self.qmaxHasErr(qmax)) {
                    result = true;
                }
            }
            return result;
        }

        allErr() {
            let self = this;
            let result = false;
            let questions = document.querySelectorAll('#root_absolute_q #tableBlock tbody');
            this.errorsArr.length = 0;

            // Hide tooltip
            $('body#page-mod-assign-view .tooltip').tooltip({trigger: 'manual'}).tooltip('hide');

            $('body#page-grade-grading-manage .tooltip').tooltip({trigger: 'manual'}).tooltip('hide');

            questions.forEach(function(el) {
                const qmax = el.querySelector('#root_absolute_q .max-point-input');
                const allSubqMax = el.querySelectorAll('#root_absolute_q .sub-points-input');

                let subqSum = 0;
                allSubqMax.forEach(function(sub) {
                    if (self.subqmaxHasErr(sub)) {
                        result = true;
                    }
                    subqSum = subqSum + +sub.value;
                });

                if (+subqSum !== +qmax.value && +subqSum !== 0) {
                    qmax.setAttribute('data-original-title', self.state.translate.err_sub_sum);
                    $(qmax).tooltip({trigger: 'manual'}).tooltip('show');
                    result = true;
                } else {
                    if (self.qmaxHasErr(qmax)) {
                        result = true;
                    }
                }
            });

            if (this.maxgradeErr()) {
                result = true;
                this.errorsArr.push('maxgrade');
            }

            if (this.grouppasErr()) {
                result = true;
            }

            if (this.gradingMethodsErr()) {
                result = true;
                this.errorsArr.push('gradingMethod');
            }

            this.errSetState();

            return result;
        }

        gradingMethodsErr() {
            const state = this.obsItem.getState();
            return +state.method === 0 ? true : false;
        }

        maxgradeErr() {
            const state = this.obsItem.getState();
            if (state.output && state.output.totalmax && state.maxgrade) {
                if (+state.maxgrade < state.output.totalmax) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        errSetState() {
            let state = this.obsItem.getState();
            let errorsArr = this.errorsArr;

            if (errorsArr.length === 0) {
                state.output.error = '';
                this.obsItem.setState(state);
                return;
            }

            let errorStr = [];
            errorsArr.forEach((el) => {
                if (el === 'maxgrade') {
                    errorStr.push(`${state.translate.error}: ${state.translate.err_total_max_grade}`);
                }
                if (el === 'gradingMethod') {
                    errorStr.push(`${state.translate.error}: ${state.translate.err_grading_method}`);
                }
            });

            state.output.error = errorStr.join(";");

            this.obsItem.setState(state);
        }

        isIntPositiveNum(obj) {
            let value = obj.value;
            let zero = ('zero' in obj) ? obj.zero : true;

            if (!isNaN(value)) {
                value = +value;
            } else {
                return false;
            }

            if (isFinite(value) && value === parseInt(value, 10)) {
                if (zero && value >= 0) {
                    return true;
                }
                if (!zero && value > 0) {
                    return true;
                }
            }
            return false;
        }

        grouppasErr() {
            let self = this;
            let result = false;

            // Draw tooltips
            let allGrouppasEls = document.querySelectorAll("#root_absolute_q .select-grouppas");

            allGrouppasEls.forEach(function(el) {
                if (+el.selectedIndex === 0) {
                    result = true;
                    el.setAttribute('data-original-title', self.state.translate.must_be_number);
                    $(el).tooltip({trigger: 'manual'}).tooltip('show');
                } else {
                    $(el).tooltip({trigger: 'manual'}).tooltip('hide');
                }
            });

            return result;
        }
    };
});