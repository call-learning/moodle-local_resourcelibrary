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

/**
 * Local Resource Library API
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_course\external\course_module_summary_exporter;
use local_resourcelibrary\external\course_summary_simple_exporter;
use local_resourcelibrary\locallib\customfield_utils;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/course/externallib.php");
require_once("lib.php");

/**
 * Resource Library external functions
 *
 * This will use internally the course api and filter out the courses or modules that don't match
 * the filters.?
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourcelibrary_external extends external_api {


    /**
     * Generic filter parameters (common to activities and courses)
     *
     * @param string $parentid
     * @param string $parentiddesc
     * @return external_function_parameters
     */
    protected static function get_filter_generic_parameters($parentid, $parentiddesc) {
        return new external_function_parameters(
            [$parentid => new external_value(PARAM_INT, $parentiddesc),
                'filters' => new external_multiple_structure (
                    new external_single_structure(
                        [
                            'type' => new external_value(PARAM_ALPHANUM,
                                'Filter type as per customfield/fields/ type class or another value like
                                globalsearch, ...'),
                            'shortname' => new external_value(PARAM_ALPHANUMEXT,
                                'Matching customfield shortname if it is a customfield filter',
                                VALUE_OPTIONAL),
                            'operator' => new external_value(PARAM_INT,
                                'Filter option as per local_resourcelibrary\filters class option
                                (this will be EQUAL, CONTAINS, NOTEQUAL...'),
                            'value' => new external_value(PARAM_RAW, 'the value of the filter to look for.'),
                        ]
                    ),
                    'Filter the results',
                    VALUE_OPTIONAL
                ),
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'sorting' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'column' => new external_value(PARAM_ALPHANUM,
                                'Column name for the sorting'),
                            'order' => new external_value(PARAM_ALPHA,
                                'ASC for ascending, DESC for descending, ascending by default'
                            ),
                        ]
                    ),
                    'Sort the results',
                    VALUE_OPTIONAL
                ),
            ]
        );
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_filtered_courses_parameters() {
        return static::get_filter_generic_parameters('categoryid', 'category id');
    }

    /**
     * Get courses
     *
     * All returned fields are available to the template.
     * @param int $categoryid
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param array $sorting
     * @return array of visible courses (whichever is the context)
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @since Moodle 2.2
     */
    public static function get_filtered_courses($categoryid = 0, $filters = [], $limit = 0, $offset = 0, $sorting = []) {
        global $CFG, $PAGE;
        require_once($CFG->dirroot . "/course/lib.php");

        // Validate parameter.
        $inparams = compact(['categoryid', 'filters', 'limit', 'offset', 'sorting']);
        self::validate_parameters(self::get_filtered_courses_parameters(), $inparams);

        // Retrieve courses.

        $sqlparams = [];
        // Simplification here: we return only visible courses, whichever is the context.
        $sqlwhere = " e.id != " . SITEID . " ";
        if ($categoryid) {
            $coursecat = core_course_category::get($categoryid);
            $children = $coursecat->get_all_children_ids();
            $children[] = $categoryid;
            $sqlwhere .= " AND e.category IN (" . implode(',', $children) . ") ";
        }

        $coursefields = ['fullname', 'shortname', 'format', 'showgrades', 'newsitems', 'startdate', 'enddate', 'maxbytes',
            'showreports', 'visible', 'groupmode', 'groupmodeforce', 'defaultgroupingid', 'enablecompletion', 'completionnotify',
            'lang', 'theme', 'marker', 'category', 'summary', 'summaryformat', 'sortorder', 'idnumber', 'timecreated',
            'timemodified', ];
        $additionalfields = ['course_categoryname' => 'ccat.name AS course_categoryname'];
        foreach ($coursefields as $cfield) {
            $additionalfields[$cfield] = "e.{$cfield} AS {$cfield}";
        }
        $handler = \core_course\customfield\course_handler::create();
        $sortsql = self::get_sort_options_sql($sorting, array_keys($additionalfields));

        $courses = customfield_utils::get_records_from_handler($handler, $filters, 0, 0,
            ['LEFT JOIN {course_categories} ccat ON e.category = ccat.id'],
            $additionalfields,
            $sqlwhere,
            $sqlparams,
            $sortsql);

        // Create return value.
        $coursesinfo = [];
        $sequenceid = 0;

        $invisiblecourseidlist = [];
        if ($invisiblecoursesids = get_config('local_resourcelibrary', 'hiddencoursesid')) {
            $invisiblecourseidlist = explode(',', $invisiblecoursesids);
        }
        if ($managementhiddenlist = self::get_hidden_items()) {
            $invisiblecourseidlist = array_merge($invisiblecourseidlist, $managementhiddenlist);
        }
        foreach ($courses as $course) {

            if (in_array($course->id, $invisiblecourseidlist)) {
                continue; // Skip invisible courses.
            }
            // Now security checks.
            $context = context_course::instance($course->id, IGNORE_MISSING);
            $hasvalidatedcontext = true;
            try {
                self::validate_context($context);
                $PAGE->set_context($context);
            } catch (Exception $e) {
                $hasvalidatedcontext = false;
                $PAGE->set_context(context_system::instance());
            }
            $coursevisible = $course->visible;
            $coursevisible = $coursevisible || has_any_capability([
                    'moodle/course:update', 'moodle/course:viewhiddencourses', 'moodle/course:view', ], $context)
                || is_enrolled($context);
            if (!$coursevisible) {
                continue;
            }
            // Here we use a simplified version for performance reasons.
            $exporter = new course_summary_simple_exporter($course, ['context' => $context]);
            $renderer = $PAGE->get_renderer('core');
            $courseinfo = (array) $exporter->export($renderer);
            $courseinfo['parentid'] = $course->category;
            $courseinfo['parentsortorder'] = $course->sortorder;
            $courseinfo['customfields'] = [];
            $courseinfo['resourcelibraryfields'] = [];
            $coursesinfo[] = $courseinfo;

        }

        return array_slice($coursesinfo, $offset, $limit ? $limit : null);
    }

    /**
     * Get the catalogue items that are hidden from the catalogue.
     * @return array of course ids that are hidden.
     */
    public static function get_hidden_items() {
        GLOBAL $DB;
        $sql = "SELECT itemid FROM {local_resourcelibrary} WHERE itemtype = :itemtype AND visibility = :visibility";
        $params = ['itemtype' => LOCAL_RESOURCELIBRARY_ITEMTYPE_COURSE, 'visibility' => LOCAL_RESOURCELIBRARY_ITEM_HIDDEN];
        $records = $DB->get_records_sql($sql, $params);
        $hiddenitems = [];
        foreach ($records as $record) {
            $hiddenitems[] = $record->itemid;
        }
        return $hiddenitems;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_filtered_courses_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'course id'),
                        'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                        'parentid' => new external_value(PARAM_INT, 'category id'),
                        'parentsortorder' => new external_value(PARAM_INT,
                            'sort order into the category', VALUE_OPTIONAL),
                        'fullname' => new external_value(PARAM_TEXT, 'full name'),
                        'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                        'image' => new external_value(PARAM_RAW, 'course image'),
                        'startdate' => new external_value(PARAM_INT,
                            'timestamp when the course start'),
                        'enddate' => new external_value(PARAM_INT,
                            'timestamp when the course end'),
                        'visible' => new external_value(PARAM_INT,
                            '1: available to student, 0:not available', VALUE_OPTIONAL),
                        'timecreated' => new external_value(PARAM_INT,
                            'timestamp when the course have been created', VALUE_OPTIONAL),
                        'timemodified' => new external_value(PARAM_INT,
                            'timestamp when the course have been modified', VALUE_OPTIONAL),
                        'viewurl' => new external_value(PARAM_URL, 'The course URL'),
                    ], 'course'
                )
            );
    }

    /**
     * Get Sort option for the SQL query
     *
     * @param array $sortoptions
     * @param array $fields
     * @return string
     */
    protected static function get_sort_options_sql($sortoptions, $fields) {
        $sortsqls = [];
        foreach ($sortoptions as $sort) {
            $order = strtoupper($sort['order']);
            $column = $sort['column'];
            if (!in_array($column, $fields) || ($order != 'ASC' && $order != 'DESC')) {
                continue; // Invalid filter, we carry on.
            }
            $sortsqls[] = "$column $order";
        }
        $sortsql = implode(',', $sortsqls);
        return $sortsql;
    }
}

