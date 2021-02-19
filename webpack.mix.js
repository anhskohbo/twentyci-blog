let mix = require('laravel-mix');

/**
 * The externals library.
 *
 * @type {Object}
 */
const externals = {};

mix.js('resources/js/app.js', 'public/js');
mix.sass('resources/sass/app.scss', 'public/css');

mix.sourceMaps(true, 'source-map');
mix.disableSuccessNotifications();

mix.browserSync({
  proxy: process.env.MIX_BROWSER_SYNC_URL || 'http://blog.local',
});

mix.webpackConfig({
  externals,
  output: {
    libraryTarget: 'this',
  },
});

mix.options({
  processCssUrls: false,
});
