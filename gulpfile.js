var gulp = require('gulp');

var clean = require('gulp-clean');
var zip = require('gulp-zip');

var bases = {
    root: 'dist/'
};

var paths = [
    'core/**',
    '!core/data',
    '!core/data/**/*',
    '!core/logs/**/*.txt',
    '!core/logs/**/*.pdf',
    '!core/logs/**/*.html',
    '!core/tests',
    '!core/tests/**/*',
    'admin/**',
    '!admin/api/config',
    '!admin/api/config/**/*',
    'boxoffice/**',
    '!boxoffice/api/config',
    '!boxoffice/api/config/**/*',
    'customer/**',
    '!customer/api/config',
    '!customer/api/config/**/*',
    'scanner/**',
    '!scanner/api/config',
    '!scanner/api/config/**/*',
    'printer/**',
    '!printer/api/config',
    '!printer/api/config/**/*'
];

gulp.task('clean', function() {
    return gulp.src(bases.root)
        .pipe(clean({}));
});

gulp.task('collect', function() {
    return gulp.src(paths, { base: './', dot: true })
        .pipe(gulp.dest(bases.root));
});

gulp.task('zip', function() {
    return gulp.src(bases.root + '**')
        .pipe(zip('ticketbox-server-php.zip'))
        .pipe(gulp.dest(bases.root));
});

gulp.task('default', gulp.series('clean', 'collect', 'zip'));
