define([], function() {
    window.requirejs.config({
        paths: {
            "axe_core": M.cfg.wwwRoot + '/local/lib/editor/atto/plugins/a11yaxe/amd/build/axe.min',
        },
        shim: {
            'axe_core': {exports: 'Axe'},
        }
    });
});