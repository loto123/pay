let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
    // .sourceMaps()                                       // 开发环境打开  生产环境注释掉
    .extract(['vue','axios','moment','mint-ui'])
    .sass('resources/assets/sass/app.scss', 'public/css');
    // ;

// mix.js('resources/assets/js/app.js', 'public/js')
//    .sourceMaps();

// mix.webpackConfig({
//     resolve: {
//         alias: {
//             'components': 'assets/js/components',
//             'config': 'assets/js/config',
//             'lang': 'assets/js/lang',
//             'plugins': 'assets/js/plugins',
//             'vendor': 'assets/js/vendor',
//             'views': 'assets/js/views',
//         },
//         modules: [
//             'node_modules',
//             path.resolve(__dirname, "resources")
//         ]
//     }
// });
