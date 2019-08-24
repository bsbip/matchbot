const path = require('path');

module.exports = {
    // proxy API requests to Valet during development
    devServer: {
        proxy: {
            '/api': {
                target: 'http://127.0.0.1:8001',
                ws: false,
                changeOrigin: true,
            },
        },
        disableHostCheck: true,
    },
    configureWebpack: {
        resolve: {
            alias: {
                vue$: 'vue/dist/vue.runtime.esm.js',
                '@shared': path.resolve('src/components/Shared'),
            },
        },
    },

    // output built static files to Laravel's public dir.
    // note the "build" script in package.json needs to be modified as well.
    outputDir: '../public',

    // modify the location of the generated HTML file.
    // make sure to do this only in production.
    indexPath:
        process.env.NODE_ENV === 'production'
            ? '../resources/views/app.blade.php'
            : 'index.html',
};
