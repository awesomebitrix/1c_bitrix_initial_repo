'use strict';

import gulp from 'gulp';
import gulpLoadPlugins from 'gulp-load-plugins';
import sequence from 'run-sequence'
import browserSync from 'browser-sync';
import rimraf from 'rimraf';

const $ = gulpLoadPlugins();
const reload = browserSync.reload;


gulp.task('default', () => {
  sequence(
    'copy',
    'blocks',
    'templates',
    'stylesheets',
    'javascripts'
  );

  gulp.watch('source/**/*.html', ['templates', reload]);
  gulp.watch('source/.blocks/*.coffee', ['blocks:javascripts', reload]);
  gulp.watch('source/.blocks/*.styl', ['blocks:stylesheets', reload]);
  gulp.watch('source/javascripts/*.coffee', ['javascripts', reload]);
  gulp.watch('source/stylesheets/*.styl', ['stylesheets', reload]);
  gulp.watch('source/images/**/*.{svg,png,jpg}', ['copy:images', reload]);
  gulp.watch('source/vendor/stylesheets/*.css', ['copy:stylesheets', reload]);
});

gulp.task('server', () => {
  browserSync({
    notify: false,
    server: {
      baseDir: ['../web/']
    }
  });
});

gulp.task('blocks', () => sequence(
  'blocks:stylesheets',
  'blocks:javascripts'
));


gulp.task('copy', () => sequence(
  'copy:stylesheets',
  'copy:javascripts',
  'copy:fonts',
  'copy:images'
));

gulp.task('copy:fonts', () => {
  return gulp.src('source/fonts/*.*')
    .pipe(gulp.dest('../web/frontend/fonts/'))
    .pipe(gulp.dest('../web/local/templates/main/fonts'))

});

gulp.task('copy:stylesheets', () => {
  return gulp.src('source/vendor/stylesheets/*.css')
    .pipe($.sourcemaps.init())
    .pipe($.cssmin())
    .pipe($.rename({suffix: '.min'}))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../web/frontend/stylesheets'))
    .pipe(gulp.dest('../web/local/templates/main/stylesheets'))

});

gulp.task('copy:javascripts', () => {
  return gulp.src([
      'angular.js',
      'angular-route.js',
      'jquery.js',
      'jquery.bem.js',
      'jquery.magnific-popup.js',
      'jquery.qtip.js',
      'd3.js',
      'underscore.js',
      'fontsmoothie.js',
    ], {cwd: 'source/vendor/javascripts/'})
    .pipe($.sourcemaps.init())
    .pipe($.uglify())
    .pipe($.concat('build.min.js'))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../web/frontend/javascripts/'))
    .pipe(gulp.dest('../web/local/templates/main/javascripts'))

});

gulp.task('templates', () => {
  return gulp.src('source/**/*.html')
    .pipe($.swig({ defaults: { cache: false }, load_json: true}))
    .pipe($.posthtml([
      require('posthtml-bem')({
        elemPrefix: '__',
        modPrefix: '_',
        modDlmtr: '_'
      })
    ]))
    .pipe(gulp.dest('../web/frontend/'))
});

gulp.task('blocks:javascripts', () => {
  return gulp.src('source/.blocks/*.coffee')
    .pipe($.plumber())
    .pipe($.sourcemaps.init())
    .pipe($.coffee())
    .pipe($.uglify())
    .pipe($.concat('application.blocks.min.js'))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../web/frontend/javascripts'))
    .pipe(gulp.dest('../web/local/templates/main/javascripts'))

});

gulp.task('blocks:stylesheets', () => {
  return gulp.src('source/.blocks/*.styl')
    .pipe($.sourcemaps.init())
    .pipe($.stylus())
    .pipe($.concat('application.blocks.min.css'))
    .pipe($.autoprefixer())
    .pipe($.cssmin())
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../web/frontend/stylesheets'))
    .pipe(gulp.dest('../web/local/templates/main/stylesheets'))

});

gulp.task('stylesheets', () => {
  return gulp.src('source/stylesheets/*.styl')
    .pipe($.sourcemaps.init())
    .pipe($.stylus())
    .pipe($.autoprefixer())
    .pipe($.cssmin())
    .pipe($.rename({suffix: '.min'}))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../web/frontend/stylesheets'))
    .pipe(gulp.dest('../web/local/templates/main/stylesheets'))
});

gulp.task('clean', () => {
  gulp.src([
    '../web/frontend',
    '../web/local/templates/main/stylesheets',
    '../web/local/templates/main/javascripts',
    '../web/local/templates/main/images',
    '../web/local/templates/main/fonts'
  ]).pipe($.clean({force: true}))
});

gulp.task('javascripts', () => {
  return gulp.src('source/javascripts/*.coffee')
    .pipe($.sourcemaps.init())
    .pipe($.coffee())
    .pipe($.uglify())
    .pipe($.rename({suffix: '.min'}))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../web/frontend/javascripts'))
    .pipe(gulp.dest('../web/local/templates/main/javascripts'))
});

gulp.task('copy:images', () => {
  return gulp.src('source/images/**/*.{svg,png,jpg}')
    .pipe($.flatten())
    .pipe($.imagemin({
      progressive: true,
      interlaced: true
    }))
    .pipe(gulp.dest('../web/frontend/images'))
    .pipe(gulp.dest('../web/local/templates/main/images'))
});
