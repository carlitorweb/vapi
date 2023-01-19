const { src, dest, series } = require('gulp');
const zip = require('gulp-zip');
const rm = require('gulp-rm');
const xml2json = require('gulp-xml2json');
const rename = require('gulp-rename');
const pkgVapi = require('./pkg_vapi.json');

/**
 * Prepare for zip the extensions
 */
function cleanAndBuild() {
    // Clean the output zip package and the previous json manifest
    src(['./packages/*', './pkg_vapi.json', './dist/*'], { read: false }).pipe(rm());

    // Build a new json manifest
    return src('./pkg_vapi.xml')
        .pipe(xml2json())
        .pipe(rename({ extname: '.json' }))
        .pipe(dest('.'));
}

function zipComponent() {
    return src('./extensions/com_vapi/**/*')
        .pipe(zip('com_vapi.zip'))
        .pipe(dest('./packages'));
}

function zipPlugin() {
    return src('./extensions/plg_webservices_vapi/**/*')
        .pipe(zip('plg_webservices_vapi.zip'))
        .pipe(dest('./packages'));
}

function zipPackage() {
    return src(['./packages/*', './pkg_vapi.xml'])
        .pipe(zip(`${pkgVapi.extension.name[0]}_${pkgVapi.extension.version}.zip`))
        .pipe(dest('./dist'));
}

exports.default = series(cleanAndBuild, zipComponent, zipPlugin, zipPackage);
