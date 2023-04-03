function loadJs() {
    var script = document.createElement('script');
    script.src = "https://widgets.algebrakit.eu/akit-widgets.min.js";
    document.body.appendChild(script);
}

if (typeof(AlgebraKIT) == 'undefined') {
    var AlgebraKIT = {
        config: {
            minified: false,
            theme: 'akit',  //algebrakit theme is default
            widgets: [{
                name: 'akit-formula-editor',
                handwriting: false
            }],
            loggingLevel: 4
        }
    };
    loadJs();
}
else if (!AlgebraKIT._api) {
    loadJs();
}