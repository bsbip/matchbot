const path = require('path');

module.exports = {
    // proxy API requests to Valet during development
    devServer: {
        writeToDisk: true,
    },
    configureWebpack: {
        resolve: {
            alias: {
                vue$: 'vue/dist/vue.runtime.esm.js',
                '@shared': path.resolve('src/components/Shared'),
                '@service': path.resolve('src/services'),
            },
        },
    },

    // output built static files to Laravel's public dir.
    // note the "build" script in package.json needs to be modified as well.
    outputDir: '../public',

    // modify the location of the generated HTML file.
    // make sure to do this only in production.
    indexPath: '../resources/views/app.blade.php',
};
