<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_resourcelibrary\external;

use context_course;
use context_system;
use core\exception\moodle_exception;
use core_course\customfield\course_handler;
use core_course_category;
use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_resourcelibrary\local\api\course_filter_api;
use local_resourcelibrary\local\externalhelper;

/**
 * Get filtered course content for the catalogue
 *
 * @copyright  2025 CALL Learning 2025 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    local_resourcelibrary
 */
class get_filtered_courses extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function execute_parameters() {
        return externalhelper::get_filter_generic_parameters('categoryid', 'category id');
    }

    /**
     * Get courses
     *
     * All returned fields are available to the template.
     *
     * @param int $categoryid
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param array $sorting
     * @return array of visible courses (whichever is the context)
     * @since Moodle 2.2
     */
    public static function execute(
        int $categoryid = 0,
        array $filters = [],
        int $limit = 0,
        int $offset = 0,
        array $sorting = []
    ) {
        // Validate parameter.
        $inparams = compact(['categoryid', 'filters', 'limit', 'offset', 'sorting']);
        [
            'categoryid' => $categoryid,
            'filters' => $filters,
            'limit' => $limit,
            'offset' => $offset,
            'sorting' => $sorting
        ] = self::validate_parameters(self::execute_parameters(), $inparams);

        // Use the new API to get filtered courses
        return course_filter_api::get_filtered_courses(0, $filters, $limit, $offset, $sorting, $categoryid);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function execute_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'course id'),
                        'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                        'parentid' => new external_value(PARAM_INT, 'category id'),
                        'parentsortorder' => new external_value(
                            PARAM_INT,
                            'sort order into the category',
                            VALUE_OPTIONAL
                        ),
                        'fullname' => new external_value(PARAM_TEXT, 'full name'),
                        'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                        'image' => new external_value(PARAM_RAW, 'course image'),
                        'startdate' => new external_value(
                            PARAM_INT,
                            'timestamp when the course start'
                        ),
                        'enddate' => new external_value(
                            PARAM_INT,
                            'timestamp when the course end'
                        ),
                        'visible' => new external_value(
                            PARAM_INT,
                            '1: available to student, 0:not available',
                            VALUE_OPTIONAL
                        ),
                        'timecreated' => new external_value(
                            PARAM_INT,
                            'timestamp when the course have been created',
                            VALUE_OPTIONAL
                        ),
                        'timemodified' => new external_value(
                            PARAM_INT,
                            'timestamp when the course have been modified',
                            VALUE_OPTIONAL
                        ),
                        'viewurl' => new external_value(PARAM_URL, 'The course URL'),
                    ],
                    'course'
                )
            );
    }
}
