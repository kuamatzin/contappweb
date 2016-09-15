var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss');
    mix.scripts([
        //'../bower/jquery/dist/jquery.js',
        '../bower/bootstrap-sass/assets/javascripts/bootstrap.js',
        '../bower/moment/min/moment.min.js',
        '../bower/bootstrap-daterangepicker/daterangepicker.js',
        '../bower/vue/dist/vue.min.js',
        '../bower/vue-resource/dist/vue-resource.min.js',
        '../bower/sweetalert/dist/sweetalert.min.js'
    ], 'public/js/vendor.js');
    mix.copy('resources/assets/bower/font-awesome/fonts', 'public/fonts');
    mix.copy('resources/assets/bower/bootstrap-sass/assets/fonts', 'public/fonts');
});
