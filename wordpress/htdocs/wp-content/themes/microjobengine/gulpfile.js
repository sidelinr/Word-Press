const gulp = require('gulp');
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const plumber = require('gulp-plumber');
const concat = require('gulp-concat');
const replace = require('gulp-replace');
const bump = require('gulp-bump');
const zip = require('gulp-zip');
const git = require('gulp-git');
const gutil = require('gulp-util');
const browserSync = require('browser-sync').create();

const config = require('./gulpfile-config');

/*************************************************
 * HELPERS
 *************************************************/
gulp.task('default', ['browser-sync']);

gulp.task('reload', function(done) {
  browserSync.reload();
  done();
});

/*************************************************
 * STYLE TASKS
 *************************************************/
gulp.task('sass', () => {
  return gulp.src(config.style.src)
    .pipe(plumber())
    .pipe(sass())
    .pipe(autoprefixer())
    .pipe(gulp.dest(config.style.dest))
    .pipe(browserSync.stream());
});

gulp.task('sass-skin', () => {
  return gulp.src(config.style.skin.src)
    .pipe(plumber())
    .pipe(sass())
    .pipe(autoprefixer())
    .pipe(gulp.dest(config.style.skin.dest))
    .pipe(browserSync.stream());
});

gulp.task('css-vendor', () => {
  return gulp.src(config.style.vendorSrc)
    .pipe(plumber())
    .pipe(concat(config.style.vendorFileName))
    .pipe(gulp.dest(config.style.dest))
});

gulp.task('style', ['sass', 'sass-skin', 'css-vendor']);

gulp.task('watch', function() {
    gulp.watch(config.style.src, ['sass']).on('change', browserSync.reload);
    gulp.watch(config.style.skin.src, ['sass-skin']).on('change', browserSync.reload);
});

/*************************************************
 * DEVELOPMENT
 *************************************************/
gulp.task('browser-sync', function() {
  browserSync.init({
    proxy: config.dev.proxy,
    port: config.dev.port
  });
  gulp.run('watch');
});

gulp.task('dev', ['style', 'browser-sync'], function() {
  gulp.watch(config.style.src, ['sass']);
  gulp.watch(config.style.skin.src, ['sass-skin']);
  gulp.watch(config.js.src, ['reload']);
  //gulp.watch(config.watch, ['reload']);
});

/*************************************************
 * RELEASE TASKS
 *************************************************/
// Auto bump version
gulp.task('bump-version', (done) => {
  if(typeof gutil.env.version !== 'undefined') {
    gulp.src(['./functions.php'])
      .pipe(replace(/define\('ET_VERSION', '([\d\.]+)'\);/g, 'define(\'ET_VERSION\', \'' + gutil.env.version + '\');'))
      .pipe(gulp.dest('./'));

    gulp.src(['./package.json', './style.css'])
      .pipe(plumber())
      .pipe(bump({ version: gutil.env.version }))
      .pipe(gulp.dest('./'));

    done();
  } else {
    done('Missing argument --version=1.0.0');
  }
});

// Create changelog
gulp.task('changelog', () => {
  var preVersion = (typeof gutil.env.preversion !== 'undefined') ? gutil.env.preversion : '1.0';
  var version = (typeof gutil.env.version !== 'undefined') ? gutil.env.version : '1.0';
  git.exec({ args: 'diff --name-only --diff-filter=AM ' + config.git.tagName + preVersion + ' HEAD > changelog-v' + version + '.txt' }, (err, stdout) => {
    if (err) throw err;
  })
});

// Zip theme
gulp.task('zip-theme', () => {
  var version = (typeof gutil.env.version !== 'undefined') ? gutil.env.version : '1.0';
  var fileName = config.zip.fileName.replace('{version}', version);
  return gulp.src(config.zip.src)
    .pipe(zip(fileName))
    .pipe(gulp.dest(config.zip.dest));
});


// gulp release --preversion=1.0 --version=1.1"
gulp.task('release', ['style', 'bump-version', 'changelog', 'zip-theme']);