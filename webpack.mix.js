let mix = require('laravel-mix');
require('laravel-mix-purgecss');
require('laravel-mix-tailwind');

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

mix.setResourceRoot('packages/protoqol/sequel')
    .postCss('resources/assets/css/app.css', 'public')
    .purgeCss()
    .tailwind()
    .js('resources/assets/js/app.js', 'public')
    .vue({ version: 2 })
    .copy('public', '../Sequel/public/vendor/sequel')
    .copy('resources/lang', '../Sequel/resources/lang/vendor/sequel');

