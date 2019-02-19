var Encore = require('@symfony/webpack-encore');
var MergeIntoSingleFilePlugin = require('webpack-merge-and-include-globally');

var uglifyJS = require("uglify-js");

var uglifyJSOptions = {
    sourceMap: true,
    mangle: false
};

const $ = require('jquery');

Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .addStyleEntry('app', './assets/scss/app.scss')

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()
    .enableVersioning()

    .enableSingleRuntimeChunk()
    .autoProvidejQuery()
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
    })

    .addPlugin(new MergeIntoSingleFilePlugin({
        files: {
            "app.js": [
                'node_modules/jquery/dist/jquery.min.js',
                'node_modules/popper.js/dist/umd/popper.min.js',
                'node_modules/bootstrap/dist/js/bootstrap.min.js',
                'node_modules/bootstrap-confirmation2/dist/bootstrap-confirmation.min.js',
                'public/bundles/futlibrary/js/bootbox.min.js'
            ],
        },
        ordered: true,
        transform: {
            'app.js': code => uglifyJS.minify(code, uglifyJSOptions).code,
        }
    }))
;

var config = Encore.getWebpackConfig();

config.watchOptions = {poll: true, ignored: /node_modules/};
config.resolve.symlinks = false;

module.exports = config;
