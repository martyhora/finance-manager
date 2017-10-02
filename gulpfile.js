'use strict';

var gulp = require('gulp');

var phplint = require('phplint').lint;
var gutil = require('gulp-util');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var livereload = require('gulp-livereload');
var runSequence = require('run-sequence');
var concatCss = require('gulp-concat-css');
var cssmin = require('gulp-cssmin');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');

var paths = {
    js: [
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/jquery-ui/jquery-ui.min.js',
        'bower_components/jquery-ui/ui/i18n/datepicker-cs.js',
        'bower_components/AdminLTE/dist/js/app.min.js',
        'bower_components/nette.ajax.js/nette.ajax.js',
        'bower_components/nette-forms/src/assets/netteForms.js',
        'www/js/*.js',
        'vendor/voda/date-input/dateInput.js',
        'bower_components/AdminLTE/dist/js/app.min.js'
    ],    
    css: [
        'bower_components/AdminLTE/bootstrap/css/bootstrap.min.css',
        'bower_components/AdminLTE/dist/css/AdminLTE.min.css',
        'bower_components/AdminLTE/dist/css/skins/_all-skins.min.css',
        'bower_components/jquery-ui/themes/overcast/jquery-ui.min.css',        
        'www/build/css/*.css', 
		    '!www/build/css/bundle.css',
        'vendor/voda/date-input/dateInput.css',
    ],
    php: [
        'app/**/*.php',
        '!node_modules/**/*',
        '!vendor/**/*'
    ],
    html: [
        'index.html',
        'app/**/*.latte',
    ]
};

gulp.task('phplint', function (cb) {
    phplint(paths.php,  { }, function (err, stdout, stderr) {
        if (err) {
            cb(err);
        } else {
            cb();
        }
    });
});

gulp.task('concat', function() {
  return gulp.src(paths.js)
    .pipe(sourcemaps.init())
    .pipe(concat('bundle.js'))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('www/build/js/'))    
    .pipe(livereload())
});

gulp.task('compress', ['concat'], function() {
  return gulp.src('www/build/js/bundle.js')
    .pipe(uglify().on('error', gutil.log))
    .pipe(gulp.dest('www/build/js'))  
    .pipe(livereload())
});

gulp.task('serve', function() {
    livereload.listen({auto: true, silent: true});

    gulp.watch(paths.php).on('change', livereload.changed);
    gulp.watch(paths.html).on('change', livereload.changed);
    gulp.watch("www/js/*.js", ['concat']);    
    gulp.watch('www/scss/*.scss', ['cssmin']);
});

gulp.task('concatCss', ['sass'], function () {
  return gulp.src(paths.css)
    .pipe(concatCss("bundle.css"))
    .pipe(gulp.dest('www/build/css'))    
    .pipe(livereload());
});

gulp.task('cssmin', ['concatCss'], function () {
    return gulp.src('www/build/css/bundle.css')
        .pipe(cssmin())        
        .pipe(gulp.dest('www/build/css'));
});

gulp.task('sass', function () {
  return gulp.src('www/scss/*.scss')
    .pipe(sass({ outputStyle : 'compressed' }).on('error', sass.logError))
    .pipe(gulp.dest('www/build/css'));
});

gulp.task('default', ['serve']);