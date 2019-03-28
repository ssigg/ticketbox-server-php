var gulp = require('gulp');

var clean = require('gulp-clean');
var zip = require('gulp-zip');

var bases = {
    root: 'dist/'
};

var paths = [
    'core/actions/*',
    'core/common/**',
    'admin/**',
    '!admin/config/*',
    'boxoffice/**',
    '!boxoffice/config/*',
    'customer/**',
    '!customer/config/*',
    'scanner/**',
    '!scanner/config/*',
    'printer/**',
    '!printer/config/*',
    'core/model/*',
    'core/services/*',
    'vendor/**',
    'composer.lock',
    'core/dependencies.php'
];

gulp.task('clean', function() {
    return gulp.src(bases.root)
        .pipe(clean({}));
});

gulp.task('collect', function() {
    return gulp.src(paths, { base: './' })
        .pipe(gulp.dest(bases.root));
});

gulp.task('zip', function() {
    return gulp.src(bases.root + '**')
        .pipe(zip('ticketbox-server-php.zip'))
        .pipe(gulp.dest(bases.root));
});

gulp.task('default', gulp.series('clean', 'collect', 'zip'));
