let Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/js/app.js')

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|ico|svg)$/
    })

    .cleanupOutputBeforeBuild()

    .enableSourceMaps(!Encore.isProduction())
    .enableIntegrityHashes(Encore.isProduction())
    .enableVersioning()
    .enableSassLoader()
    .enableSingleRuntimeChunk()
    .enableBuildNotifications()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .configureTerserPlugin((config) => {
        config.terserOptions = {
            format: {
                comments: true
            }
        };
        config.extractComments = true;
    })
;

module.exports = Encore.getWebpackConfig();
