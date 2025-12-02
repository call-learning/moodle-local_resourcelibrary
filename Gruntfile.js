/* eslint-env node */
/* jshint node: true */
/* jshint esversion: 6 */
module.exports = grunt => {
    const path = require('path');
    const localLibrary = require(path.join(__dirname, '.grunt', 'library.js'));
    return localLibrary.buildSass(grunt);
};