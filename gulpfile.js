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
var gutil = require('gulp-util');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var cache = require('gulp-cache');
var bump = require('gulp-bump');

cache.clearAll();

// Lint JavaScript
gulp.task('jshint', function () {
  return gulp.src('app/scripts/**/*.js')
    .pipe($.jshint())
    .pipe($.jshint.reporter('jshint-stylish'))
    .pipe($.jshint.reporter('fail'))
    .on('error', gutil.log);
});

// Optimize Images
gulp.task('images', function () {
  return gulp.src('app/images/**/*')
    // .pipe($.cache($.imagemin({
      // progressive: true,
      // interlaced: true
    // })))
    .pipe(gulp.dest('dist/images'));
});

// Automatically Prefix CSS
gulp.task('styles:css', function () {
  return gulp.src('app/styles/**/*.css')
    .pipe($.autoprefixer('last 1 version'))
    .pipe(gulp.dest('app/styles'));
});

gulp.task('styles:bootstrapfonts', function () {
  return gulp.src('app/bootstrap/fonts/**')
    .pipe(gulp.dest('dist/fonts'));
});

// Output Final CSS Styles
gulp.task('styles', ['styles:css', 'styles:bootstrapfonts']);

// Scan Your HTML For Assets & Optimize Them
gulp.task('html', function () {
  return gulp.src('app/*.php')
    .pipe($.useref.assets({searchPath: '{.tmp,app}'}))
    .pipe($.useref.restore())
    .pipe($.useref())
    // Update Production Style Guide Paths
    .pipe($.replace('components/components.css', 'components/main.min.css'))
    // Output Files
    .pipe(gulp.dest('dist'));
});

// Leave the files separated since there is only one per screen
gulp.task('scripts:main', function() {
    return gulp.src('app/scripts/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('dist/scripts'));
});

gulp.task('scripts:jquery', function() {
    return gulp.src('app/jquery/*.js')
        // It is important that jQuery come before Bootstrap so this
        // needs to come first alphabetically...hackalicious
        .pipe(concat('scripts/a-main-jquery.min.js'))
        .pipe(gulp.dest('dist'));
});

gulp.task('scripts:bootstrap', function() {
    // Just copy the already minified file to the dist folder
    return gulp.src('app/bootstrap/js/bootstrap.min.js')
        .pipe(gulp.dest('dist/scripts'));
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

gulp.task('files:utils', function() {
    return gulp.src('app/utils/*.{php,json}')
        .pipe(gulp.dest('dist/utils'));
});

gulp.task('files:config', function() {
    return gulp.src('app/config.ini')
        .pipe(gulp.dest('dist'));
});

gulp.task('files', ['files:ajax', 'files:config', 'files:utils']);

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

// Build Production Files, the Default Task
gulp.task('default', ['clean'], function (cb) {
  runSequence('styles', 'jshint', 'html', 'images', 'files', 'scripts', cb);
});
