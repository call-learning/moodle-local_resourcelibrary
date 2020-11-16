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
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_filtered_course_content_parameters() {
        return static::get_filter_generic_parameters('courseid', 'course id');
    }

    /**
     * Generic filter parameters (common to activities and courses)
     *
     * @param string $parentid
     * @param string $parentiddesc
     * @return external_function_parameters
     */
    protected static function get_filter_generic_parameters($parentid, $parentiddesc) {
        return new external_function_parameters(
            array($parentid => new external_value(PARAM_INT, $parentiddesc),
                'filters' => new external_multiple_structure (
                    new external_single_structure(
                        array(
                            'type' => new external_value(PARAM_ALPHANUM,
                                'Filter type as per customfield/fields/ type class or another value like
                                globalsearch, ...'),
                            'shortname' => new external_value(PARAM_ALPHANUM,
                                'Matching customfield shortname if it is a customfield filter',
                                VALUE_OPTIONAL),
                            'operator' => new external_value(PARAM_INT,
                                'Filter option as per local_resourcelibrary\filters class option
                                (this will be EQUAL, CONTAINS, NOTEQUAL...'),
                            'value' => new external_value(PARAM_RAW, 'the value of the filter to look for.')
                        )
                    ),
                    'Filter the results',
                    VALUE_OPTIONAL
                ),
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'sorting' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'column' => new external_value(PARAM_ALPHANUM,
                                'Column name for the sorting'),
                            'order' => new external_value(PARAM_ALPHA,
                                'ASC for ascending, DESC for descending, ascending by default'
                            ),
                        )
                    ),
                    'Sort the results',
                    VALUE_OPTIONAL
                ),
            )
        );
    }

    /**
     * Get course modules filtered
     *
     * All returned fields are available to the template.
     * @param int $courseid course id
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param array $sorting
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_filtered_course_content($courseid, $filters = array(), $limit = 0, $offset = 0, $sorting = array()) {
        global $PAGE, $DB;
        // Validate parameters.

        $inparams = compact(array('courseid', 'filters', 'limit', 'offset', 'sorting'));
        $params = self::validate_parameters(self::get_filtered_course_content_parameters(), $inparams);

        $sqlparams = array('courseid' => $courseid);
        $sqlwhere = "e.course = :courseid AND m.visible = 1"; // Only activated modules.

        $modulefields = array('section');
        $additionalfields = array();
        foreach ($modulefields as $mfield) {
            $additionalfields[] = "e.{$mfield} AS {$mfield}";
        }
        $additionalfields[] = "e.added AS timemodified"; // There is no modification time for course module.
        $additionalfields[] = "m.name AS fullname";
        $additionaljoins = ['LEFT JOIN {modules} m ON m.id = e.module'];
        $handler = local_resourcelibrary\customfield\coursemodule_handler::create();
        $sortsql = self::get_sort_options_sql($sorting, array_keys($additionalfields));
        $modules = customfield_utils::get_records_from_handler($handler, $filters, 0, 0,
            $additionaljoins,
            $additionalfields,
            $sqlwhere,
            $sqlparams,
            $sortsql);

        $modinfo = get_fast_modinfo($courseid);
        $context = \context_course::instance($courseid);
        $PAGE->set_context($context);
        $renderer = $PAGE->get_renderer('core');
        $fullmodulesinfo = [];
        $coursesimage = $renderer->get_generated_image_for_id($courseid);

        foreach ($modules as $mod) {
            $cm = $modinfo->get_cm($mod->id);
            if ($cm->uservisible) {
                $additionamoduleinfo =
                    (new course_module_summary_exporter(null, ['cm' => $cm]))->export($renderer);
                $additionamoduleinfo->modname = $cm->modname;
                $additionamoduleinfo->groupmode = $cm->groupmode;
                $additionamoduleinfo->groupingid = $cm->groupingid;
                $additionamoduleinfo->idnumber = $cm->idnumber;
                $additionamoduleinfo->fullname = $cm->name;
                $additionamoduleinfo->parentid = $cm->course;
                $additionamoduleinfo->visible = $cm->uservisible;
                $additionamoduleinfo->timemodified = $DB->get_field($cm->modname, 'timemodified', array('id' => $cm->instance));
                if ($cm->url) {
                    $additionamoduleinfo->viewurl = $cm->url->out_as_local_url();
                } else {
                    $additionamoduleinfo->viewurl = (new moodle_url('/course/view.php', array('id' => $courseid)))->out(false);
                }
                $additionamoduleinfo->image = $coursesimage;
                $fullmodulesinfo[] = array_merge((array) $mod, (array) $additionamoduleinfo);
            }
        }
        // Here as we want to sort by time modified, we need to sort the list as it is not possible
        // to do it via SQL.
        // TODO: sort the table.
        return array_slice($fullmodulesinfo, $offset, $limit ? $limit : null);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_filtered_course_content_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'course id'),
                        'parentid' => new external_value(PARAM_INT, 'parentid id (course)'),
                        'fullname' => new external_value(PARAM_TEXT, 'full name'),
                        'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                        'modname' => new external_value(PARAM_RAW, 'module name'),
                        'iconurl' => new external_value(PARAM_URL, 'module icon url'),
                        'visible' => new external_value(PARAM_INT,
                            '1: available to student, 0:not available', VALUE_OPTIONAL),
                        'image' => new external_value(PARAM_RAW, 'course image'),
                        'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                            VALUE_OPTIONAL),
                        'groupingid' => new external_value(PARAM_INT, 'grouping id',
                            VALUE_OPTIONAL),
                        'timecreated' => new external_value(PARAM_INT,
                            'timestamp when the course have been created', VALUE_OPTIONAL),
                        'timemodified' => new external_value(PARAM_INT,
                            'timestamp when the course have been modified', VALUE_OPTIONAL),
                        'viewurl' => new external_value(PARAM_URL, 'The module URL'))
                    , 'course module'
                )
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
    public static function get_filtered_courses($categoryid = 0, $filters = array(), $limit = 0, $offset = 0, $sorting = array()) {
        global $CFG, $PAGE;
        require_once($CFG->dirroot . "/course/lib.php");

        // Validate parameter.
        $inparams = compact(array('categoryid', 'filters', 'limit', 'offset', 'sorting'));
        self::validate_parameters(self::get_filtered_courses_parameters(), $inparams);

        // Retrieve courses.

        $sqlparams = array();
        // Simplification here: we return only visible courses, whichever is the context.
        $sqlwhere = " e.id != " . SITEID . " ";
        if ($categoryid) {
            $sqlwhere .= " AND e.categoryid = $categoryid ";
        }

        $coursefields = array('fullname', 'shortname', 'format', 'showgrades', 'newsitems', 'startdate', 'enddate', 'maxbytes',
            'showreports', 'visible', 'groupmode', 'groupmodeforce', 'defaultgroupingid', 'enablecompletion', 'completionnotify',
            'lang', 'theme', 'marker', 'category', 'summary', 'summaryformat', 'sortorder', 'idnumber', 'timecreated',
            'timemodified');
        $additionalfields = array('course_categoryname' => 'ccat.name AS course_categoryname');
        foreach ($coursefields as $cfield) {
            $additionalfields[$cfield] = "e.{$cfield} AS {$cfield}";
        }
        $handler = \core_course\customfield\course_handler::create();
        $sortsql = self::get_sort_options_sql($sorting, array_keys($additionalfields));

        $courses = customfield_utils::get_records_from_handler($handler, $filters, 0, 0,
            array('LEFT JOIN {course_categories} ccat ON e.category = ccat.id'),
            $additionalfields,
            $sqlwhere,
            $sqlparams,
            $sortsql);

        // Create return value.
        $coursesinfo = array();
        $sequenceid = 0;

        $invisiblecourseidlist = [];
        if ($invisiblecoursesids = get_config('local_resourcelibrary', 'hiddencoursesid')) {
            $invisiblecourseidlist = explode(',', $invisiblecoursesids);
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
                    'moodle/course:update', 'moodle/course:viewhiddencourses', 'moodle/course:view'], $context)
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
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_filtered_courses_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    array(
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
                    ), 'course'
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

