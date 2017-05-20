var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var concatCss = require('gulp-concat-css');
var cleanCSS = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');
var gulpif = require('gulp-if');
var minimist = require('minimist');
var browserSync = require('browser-sync').create();
var banner = require('gulp-banner');

// get parameters from cli
// @see http://gulpjs.org/recipes/pass-arguments-from-cli.html
var knownOptions = {
  string: 'env',
  default: { env: process.env.NODE_ENV || 'production' }
};
var options = minimist(process.argv.slice(2), knownOptions);


// Task for compiling sass. Run with 'gulp sass'
gulp.task('sass', function () {

    // include paths for sass compiler
    var sassPaths = [
        'bower_components/foundation-sites/scss',
        // 'bower_components/motion-ui/src',
        'src/scss'
    ];

    var comment = '/*\n THIS IS A GENERATED FILE, please edit and compile ./src/scss/*.scss files.\n*/\n\n';

    return gulp.src('src/scss/app.scss')
        .pipe(gulpif(options.env !== 'production', sourcemaps.init()))
        .pipe(sass({includePaths: sassPaths}))
        .pipe(gulpif(options.env !== 'production', sourcemaps.write()))
        .pipe(banner(comment))
        .pipe(gulp.dest('src/css'));
});

// Task for compiling styles. Run with 'gulp css'. as we cannot
// guarantee all style libraries will provide sass files, this task will
// allow us to bring in css files too.
gulp.task('css', function() {

    // Will compile all styles into a single file. Add files to the array,
    // styles will be compiled in that order.
    var scripts = [
        'bower_components/dripicons/css/dripicons.css',
        'bower_components/slicknav/dist/slicknav.css',
        // 'bower_components/fontawesome/css/font-awesome.css',
        'bower_components/jquery-ui/themes/base/jquery-ui.css',
        //...
        'src/css/app.css', // compiled app.sass (includes foundation css)
        'src/css/theme.css'
    ];

    return gulp.src(scripts)
        .pipe(concatCss('app.css'))
        // .pipe(gulpif(options.env === 'production', cleanCSS({compatibility: 'ie8'})))
        .pipe(gulp.dest('public/css'))
        .pipe(browserSync.stream());
});

// Task for compiling scripts, and minifying. Run with 'gulp js'
gulp.task('js', function() {

    // Will compile all scripts into a single script. Add files to the array,
    // scripts will be compiled in that order.
    var scripts = [
        'bower_components/jquery/dist/jquery.js',
        'bower_components/jquery-ui/jquery-ui.js',
        // 'bower_components/what-input/dist/what-input.js',
        'bower_components/foundation-sites/dist/js/foundation.js',
        'bower_components/slicknav/dist/jquery.slicknav.js',
        'bower_components/highcharts/highcharts.js',
        'bower_components/highcharts/modules/data.js',
        'bower_components/highcharts/modules/drilldown.js',
        //...
        'src/js/sliding-menu.js',
        'src/js/app.js'
    ];

    return gulp.src(scripts)
        .pipe(concat('app.js'))
        .pipe(gulpif(options.env === 'production', uglify()))
        .pipe(gulp.dest('public/js'))
        .pipe(browserSync.stream());
});

// gulp.task('icons', function() { 
//     return gulp.src('./bower_components/fontawesome/fonts/**.*') 
//         .pipe(gulp.dest('./public/fonts')); 
// });

gulp.task('default', function() {

    if(options.browserSync) {
        browserSync.init({
            server: {
                baseDir: "./"
            }
        });
    }

    gulp.watch(['src/scss/**/*.scss'], ['sass']);
    gulp.watch(['src/css/**/*.css'], ['css']);
    gulp.watch(['src/js/**/*.js'], ['js']);
});
