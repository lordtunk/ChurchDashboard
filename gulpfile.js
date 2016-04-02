/**
 *
 *  Web Starter Kit
 *  Copyright 2014 Google Inc. All rights reserved.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License
 *
 */

'use strict';

// Include Gulp & Tools We'll Use
var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var rimraf = require('rimraf');
var runSequence = require('run-sequence');
var browserSync = require('browser-sync');
var pagespeed = require('psi');
var gutil = require('gulp-util');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var cache = require('gulp-cache');
var reload = browserSync.reload;
var bump = require('gulp-bump');
var shell = require('gulp-shell');

cache.clearAll();

// Lint JavaScript
gulp.task('jshint', function () {
  return gulp.src('app/scripts/**/*.js')
    .pipe($.jshint())
    .pipe($.jshint.reporter('jshint-stylish'))
    .pipe($.jshint.reporter('fail'))
    .on('error', gutil.log)
    .pipe(reload({stream: true, once: true}));
});

// Optimize Images
gulp.task('images', function () {
  return gulp.src('app/images/**/*')
    .pipe($.cache($.imagemin({
      progressive: true,
      interlaced: true
    })))
    .pipe(gulp.dest('dist/images'))
    .pipe(reload({stream: true, once: true}))
    .pipe($.size({title: 'images'}));
});

// Automatically Prefix CSS
gulp.task('styles:css', function () {
  return gulp.src('app/styles/**/*.css')
    .pipe($.autoprefixer('last 1 version'))
    .pipe(gulp.dest('app/styles'))
    .pipe(reload({stream: true}))
    .pipe($.size({title: 'styles:css'}));
});

gulp.task('styles:bootstrapfonts', function () {
  return gulp.src('app/bootstrap/fonts/**')
    .pipe(gulp.dest('dist/fonts'))
    .pipe($.size({title: 'styles:bootstrapfonts'}));
});

////////////////////////////////////////////////////////////////////////
// Commented out the Sass compilation since I am not using it anymore
// It might be useful though if I ever add it back in
////////////////////////////////////////////////////////////////////////

// Compile Sass For Style Guide Components (app/styles/components)
//gulp.task('styles:components', function () {
  //return gulp.src('app/styles/components/components.scss')
    //.pipe($.rubySass({
      //style: 'expanded',
      //precision: 10,
      //loadPath: ['app/styles/components']
    //}))
    //.pipe($.autoprefixer('last 1 version'))
    //.pipe(gulp.dest('app/styles/components'))
    //.pipe($.size({title: 'styles:components'}));
//});

// Compile Any Other Sass Files You Added (app/styles)
//gulp.task('styles:scss', function () {
  //return gulp.src(['app/styles/**/*.scss', '!app/styles/components/components.scss'])
    //.pipe($.rubySass({
      //style: 'expanded',
      //precision: 10,
      //loadPath: ['app/styles']
    //}))
    //.pipe($.autoprefixer('last 1 version'))
    //.pipe(gulp.dest('.tmp/styles'))
    //.pipe($.size({title: 'styles:scss'}));
//});

// Output Final CSS Styles
//gulp.task('styles', ['styles:components', 'styles:scss', 'styles:css']);
gulp.task('styles', ['styles:css', 'styles:bootstrapfonts']);

// Scan Your HTML For Assets & Optimize Them
gulp.task('html', function () {
  return gulp.src('app/**/*.html')
    .pipe($.useref.assets({searchPath: '{.tmp,app}'}))
    // Concatenate And Minify Styles
    .pipe($.if('*.css', $.csso()))
    .pipe($.useref.restore())
    .pipe($.useref())
    // Update Production Style Guide Paths
    .pipe($.replace('components/components.css', 'components/main.min.css'))
    // Minify Any HTML
    .pipe($.minifyHtml())
    // Output Files
    .pipe(gulp.dest('dist'))
    .pipe($.size({title: 'html'}));
});

// Leave the files separated since there is only one per screen
gulp.task('scripts:main', function() {
    return gulp.src('app/scripts/*.js')
        //.pipe(concat('scripts/main-scripts.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('dist/scripts'));
});

gulp.task('scripts:jquery', function() {
    return gulp.src('app/jquery/*.js')
        .pipe(concat('scripts/main-jquery.min.js'))
        .pipe(gulp.dest('dist'));
});

gulp.task('scripts:bootstrap', function() {
    // Just copy the already minified file to the dist folder
    return gulp.src('app/bootstrap/*.min.js')
        //.pipe(concat('scripts/main-bootstrap.min.js'))
        .pipe(gulp.dest('dist'));
});

gulp.task('scripts:join', function() {
    // Join the jQuery and Bootstrap minified files
    return gulp.src('dist/scripts/*.min.js')
        .pipe(concat('scripts/main.min.js'))
        .pipe(gulp.dest('dist'));
});

gulp.task('scripts:clean', function (cb) {
    rimraf('dist/scripts', cb);
});

gulp.task('scripts', function() {
  runSequence('scripts:clean', 'scripts:main', 'scripts:jquery', 'scripts:bootstrap', 'scripts:join');
});

gulp.task('files:ajax', function() {
    return gulp.src('app/ajax/*.{php,json}')
        .pipe(gulp.dest('dist/ajax'));
});

gulp.task('files:config', function() {
    return gulp.src('app/config.ini')
        .pipe(gulp.dest('dist'));
});

gulp.task('files', ['files:ajax', 'files:config']);

gulp.task('bump-patch', function(){
  gulp.src(['./app/manifest.webapp'])
  .pipe(bump({type:'patch'}))
  .pipe(gulp.dest('./app/'));
  
  gulp.src(['./package.json'])
  .pipe(bump({type:'patch'}))
  .pipe(gulp.dest('./'));
});

gulp.task('bump-minor', function(){
  gulp.src(['./app/manifest.webapp'])
  .pipe(bump({type:'minor'}))
  .pipe(gulp.dest('./app/'));
  
  gulp.src(['./package.json'])
  .pipe(bump({type:'minor'}))
  .pipe(gulp.dest('./'));
});

gulp.task('bump-major', function(){
  gulp.src(['./app/manifest.webapp'])
  .pipe(bump({type:'major'}))
  .pipe(gulp.dest('./app/'));
  
  gulp.src(['./package.json'])
  .pipe(bump({type:'major'}))
  .pipe(gulp.dest('./'));
});

// Clean Output Directory
gulp.task('clean', function (cb) {
  rimraf('dist', rimraf.bind({}, '.tmp', cb));
});

// Watch Files For Changes & Reload
gulp.task('serve', function () {
  browserSync.init({
    proxy: "localhost:8080/GuideChurchDash/app",
    notify: false,
    host: '192.168.2.17'
  });
  
  gulp.watch(['app/**/*.html'], reload);
  gulp.watch(['app/styles/**/*.scss'], ['styles:scss']);
  gulp.watch(['app/styles/**/*.css'], ['styles:css']);
  gulp.watch(['.tmp/styles/**/*.css'], reload);
  gulp.watch(['app/scripts/**/*.js'], ['jshint']);
  gulp.watch(['app/images/**/*'], ['images']);
});

// Build Production Files, the Default Task
gulp.task('default', ['clean'], function (cb) {
  runSequence('styles', ['jshint', 'html', 'images', 'files'], 'scripts', cb);
});

// Run PageSpeed Insights
// Update `url` below to the public URL for your site
gulp.task('pagespeed', pagespeed.bind(null, {
  // By default, we use the PageSpeed Insights
  // free (no API key) tier. You can use a Google
  // Developer API key if you have one. See
  // http://goo.gl/RkN0vE for info key: 'YOUR_API_KEY'
  url: 'http://dev.gcb.my-tasks.info',
  strategy: 'mobile'
}));
