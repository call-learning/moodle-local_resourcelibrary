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
use core_course_category;
use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_resourcelibrary\local\customfield_utils;
use local_resourcelibrary\local\externalhelper;
use local_resourcelibrary\local\utils;

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
     * @param int $categoryid
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param array $sorting
     * @return array of visible courses (whichever is the context)
     * @since Moodle 2.2
     */
    public static function execute($categoryid = 0, $filters = [], $limit = 0, $offset = 0, $sorting = []) {
        global $CFG, $PAGE;
        require_once($CFG->dirroot . "/course/lib.php");

        // Validate parameter.
        $inparams = compact(['categoryid', 'filters', 'limit', 'offset', 'sorting']);
        self::validate_parameters(self::execute_parameters(), $inparams);

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
        $sortsql = externalhelper::get_sort_options_sql($sorting, array_keys($additionalfields));

        $courses = customfield_utils::get_records_from_handler(
            $handler,
            $filters,
            0,
            0,
            ['LEFT JOIN {course_categories} ccat ON e.category = ccat.id'],
            $additionalfields,
            $sqlwhere,
            $sqlparams,
            $sortsql
        );

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
            } catch (moodle_exception $e) {
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
        global $DB;
        $sql = "SELECT itemid FROM {local_resourcelibrary} WHERE itemtype = :itemtype AND visibility = :visibility";
        $params = ['itemtype' => utils::LOCAL_RESOURCELIBRARY_ITEMTYPE_COURSE, 'visibility' => utils::LOCAL_RESOURCELIBRARY_ITEM_HIDDEN];
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
