/* eslint-env node */
/* jshint node: true */
/* jshint esversion: 6 */
const path = require("path");
const {existsSync} = require('fs');
/**
 * Find the Moodle root directory by looking for a specific file.
 *
 * @param startDir
 * @param fileName
 * @return {*}
 */
const findRoot = (startDir, fileName) => {
    while (!existsSync(path.join(startDir, fileName))) {
        const parent = path.dirname(startDir);
        if (parent === startDir) {
            break;
        }
        startDir = parent;
    }
    return startDir;
};

const getModulePath = (moodleRoot) => {
    return __dirname.replace(moodleRoot + path.sep, '').replace('/.grunt', '');
};

const getModuleName = (modulePath, moodleRoot) => {
    const ComponentsList = require(path.join(moodleRoot, '.grunt', 'components.js'));
    const currentDir = process.cwd();
    process.chdir(moodleRoot);
    try {
        return ComponentsList.getComponentFromPath(modulePath);
    } finally {
        process.chdir(currentDir);
    }
};

const buildSass = (grunt) => {
    const moodleRoot = findRoot(__dirname, 'config-dist.php');
    const MODULE_PATH = getModulePath(moodleRoot);
    const MODULE_NAME = getModuleName(MODULE_PATH, moodleRoot);
    const rootGruntfile = path.join(moodleRoot, 'Gruntfile.js');
    if (grunt.file.exists(rootGruntfile)) {
        process.chdir(moodleRoot); // Change to moodle root before loading the main Gruntfile.
        // But do not change the process.env.PWD
        require(rootGruntfile)(grunt);
    }
    const config = {
        sass: {
            dist: {
                files: {}
            },
        },
        stylelint: {}
    };
    const files = {};
    files[path.join(moodleRoot, MODULE_PATH, '/styles.css')] = path.join(moodleRoot, MODULE_PATH, '/scss/styles.scss');
    config.sass[MODULE_NAME] = {
        files: files,
        options: {
            implementation: require('sass'),
            includePaths: [
                path.join(moodleRoot, MODULE_PATH, '/scss')
            ],
            sourceComments: true,
            indentWidth: 4,
            outputStyle: 'expanded',
        }
    };
    config.stylelint[MODULE_NAME] = {
        options: {
            fix: true,
            cache: false,
            failOnError: false,
            config: {
                rules: {
                    "indentation": 4,
                    "declaration-block-single-line-max-declarations": 1,
                }
            },
        },
        src: [path.join(moodleRoot, MODULE_PATH, '/styles.css')]
    };
    grunt.config.merge(config);
    grunt.registerTask('default', ['sass:' + MODULE_NAME, 'stylelint:' + MODULE_NAME]);
};

module.exports = {
    buildSass,
};
