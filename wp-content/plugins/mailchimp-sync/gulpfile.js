'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');
const rename = require("gulp-rename");
const cssmin = require('gulp-cssmin');
const source = require('vinyl-source-stream');
const browserify = require('browserify');
const replace = require('gulp-replace');
const buffer = require('vinyl-buffer');

gulp.task('sass', function () {
	var files = './assets/sass/[^_]*.scss';

	return gulp.src(files)
		// create .css file
		.pipe(sass())
		.pipe(rename({ extname: '.css' }))
		.pipe(gulp.dest('./assets/css'))

		// create .min.css
		.pipe(cssmin())
		.pipe(rename({extname: '.min.css'}))
		.pipe(gulp.dest("./assets/css"));
});

gulp.task('browserify', function () {
	return browserify({ entries: [ './assets/browserify/admin.js'] })
		.on('error', console.log)
		.transform("babelify", {presets: ["es2015"]})
		.bundle()
		.pipe(source('admin.js'))

		// create .js file
		.pipe(rename({ extname: '.js' }))
		.pipe(gulp.dest('./assets/js'));
		
});

gulp.task('uglify', gulp.series('browserify', function() {
	return gulp.src(['./assets/js/*.js','!./assets/js/*.min.js'])
		.pipe(buffer())
		.pipe(uglify().on('error', console.log))
		.pipe(rename({extname: '.min.js'}))
		.pipe(gulp.dest('./assets/js'));
}));


gulp.task('watch', function () {
	gulp.watch('./assets/sass/**.scss', gulp.series('sass'));
	gulp.watch(['./assets/js/*.js','!./assets/js/*.min.js'], gulp.series('uglify'));
	gulp.watch('./assets/js/src/**.js', gulp.series('browserify'));
});

gulp.task('default', gulp.series('sass', 'uglify'));
