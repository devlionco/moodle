define([
    'jquery',
], function($) {
    return class Toast {
        constructor() {
            if (Toast._instance) {
                return Toast._instance;
            }
            Toast._instance = this;
        }

        toastSuccessShow() {
            $('#root_absolute_q .toast').css('z-index', 5000);
            $('#root_absolute_q .toast').toast({
                animation: true,
                delay: 2000,
                autohide: true,
            }).toast('show');
        }

        toastHandle(obj) {
            $('.toast').on(obj.method, obj.fn);
        }

    };
});