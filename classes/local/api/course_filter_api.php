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
 * Course filtering API for Resource Library
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\local\api;

use context_course;
use context_system;
use core\exception\moodle_exception;
use core_course\customfield\course_handler;
use core_course_category;
use local_resourcelibrary\external\course_summary_simple_exporter;
use local_resourcelibrary\item_type;
use local_resourcelibrary\item_visibility;
use local_resourcelibrary\local\customfield_utils;
use local_resourcelibrary\local\externalhelper;
use local_resourcelibrary\local\persistent\catalogue_page;

/**
 * Course filtering API
 *
 * Provides centralized course filtering logic for the Resource Library.
 * This class consolidates all filtering, sorting, and visibility logic
 * that was previously duplicated across multiple classes.
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_filter_api {
    /**
     * Get filtered courses
     *
     * All returned fields are available to the template.
     *
     * @param int $pageid Catalogue page ID to filter by (0 for all courses)
     * @param array $filters Array of custom field filters
     * @param int $limit Maximum number of courses to return (0 for no limit)
     * @param int $offset Number of courses to skip for pagination
     * @param array $sorting Array of sorting options
     * @param int $categoryid Legacy category ID support for backward compatibility (0 to ignore)
     * @return array Array of visible courses
     */
    public static function get_filtered_courses(
        int $pageid = 0,
        array $filters = [],
        int $limit = 0,
        int $offset = 0,
        array $sorting = [],
        int $categoryid = 0
    ): array {
        global $CFG, $PAGE;
        require_once($CFG->dirroot . "/course/lib.php");

        // Build SQL WHERE clause.
        $sqlparams = [];
        $sqlwhere = " e.id != " . SITEID . " ";

        // Category filtering based on catalogue page configuration.
        if ($pageid > 0) {
            $cataloguepage = catalogue_page::get_record(['id' => $pageid]);
            if ($cataloguepage) {
                $categoryids = $cataloguepage->get_categories_array();
                if (!empty($categoryids)) {
                    $allcategories = [];
                    foreach ($categoryids as $catid) {
                        $coursecat = core_course_category::get($catid, IGNORE_MISSING);
                        if ($coursecat) {
                            $children = $coursecat->get_all_children_ids();
                            $children[] = $catid;
                            $allcategories = array_merge($allcategories, $children);
                        }
                    }
                    if (!empty($allcategories)) {
                        $allcategories = array_unique($allcategories);
                        $sqlwhere .= " AND e.category IN (" . implode(',', $allcategories) . ") ";
                    }
                }
            }
        } else if ($categoryid > 0) {
            // Legacy category filtering for backward compatibility.
            $coursecat = core_course_category::get($categoryid, IGNORE_MISSING);
            if ($coursecat) {
                $allcategories = $coursecat->get_all_children_ids();
                $allcategories[] = $categoryid;
                $allcategories = array_unique($allcategories);
                $sqlwhere .= " AND e.category IN (" . implode(',', $allcategories) . ") ";
            }
        }
        // If both pageid = 0 and categoryid = 0, show all courses in all categories.

        // Course fields to retrieve.
        $coursefields = [
            'fullname', 'shortname', 'format', 'showgrades', 'newsitems', 'startdate', 'enddate', 'maxbytes',
            'showreports', 'visible', 'groupmode', 'groupmodeforce', 'defaultgroupingid', 'enablecompletion',
            'completionnotify', 'lang', 'theme', 'marker', 'category', 'summary', 'summaryformat', 'sortorder',
            'idnumber', 'timecreated', 'timemodified',
        ];

        $additionalfields = ['course_categoryname' => 'ccat.name AS course_categoryname'];
        foreach ($coursefields as $cfield) {
            $additionalfields[$cfield] = "e.{$cfield} AS {$cfield}";
        }

        $handler = course_handler::create();
        $sortsql = externalhelper::get_sort_options_sql($sorting, array_keys($additionalfields));

        // Get courses using the custom field utility.
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

        // Custom field filtering is now handled via the $filters parameter.

        // Get list of hidden courses.
        $invisiblecourseidlist = self::get_hidden_course_ids();

        // Process and format courses.
        $coursesinfo = [];
        foreach ($courses as $course) {
            // Skip hidden courses.
            if (in_array($course->id, $invisiblecourseidlist)) {
                continue;
            }

            // Security and visibility checks.
            $context = context_course::instance($course->id, IGNORE_MISSING);
            if (!$context) {
                continue;
            }

            try {
                $PAGE->set_context($context);
            } catch (moodle_exception $e) {
                $PAGE->set_context(context_system::instance());
            }

            $coursevisible = $course->visible;
            $coursevisible = $coursevisible || has_any_capability([
                'moodle/course:update',
                'moodle/course:viewhiddencourses',
                'moodle/course:view',
            ], $context) || is_enrolled($context);

            if (!$coursevisible) {
                continue;
            }

            // Export course data.
            $exporter = new course_summary_simple_exporter($course, ['context' => $context]);
            $renderer = $PAGE->get_renderer('core');
            $courseinfo = (array) $exporter->export($renderer);
            $courseinfo['parentid'] = $course->category;
            $courseinfo['parentsortorder'] = $course->sortorder;
            $courseinfo['customfields'] = [];
            $courseinfo['resourcelibraryfields'] = [];
            $coursesinfo[] = $courseinfo;
        }

        // Apply pagination.
        return array_slice($coursesinfo, $offset, $limit ? $limit : null);
    }

    /**
     * Get the catalogue items that are hidden from the catalogue.
     *
     * @return array Array of course IDs that are hidden
     */
    public static function get_hidden_course_ids(): array {
        global $DB;

        $invisiblecourseidlist = [];

        // Get courses hidden via configuration.
        if ($invisiblecoursesids = get_config('local_resourcelibrary', 'hiddencoursesid')) {
            $invisiblecourseidlist = explode(',', $invisiblecoursesids);
            $invisiblecourseidlist = array_map('intval', $invisiblecourseidlist);
        }

        // Get courses hidden via management interface.
        $sql = "SELECT itemid FROM {local_resourcelibrary} WHERE itemtype = :itemtype AND visibility = :visibility";
        $params = [
            'itemtype' => item_type::COURSE->value,
            'visibility' => item_visibility::HIDDEN->value,
        ];
        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $invisiblecourseidlist[] = (int) $record->itemid;
        }

        return array_unique($invisiblecourseidlist);
    }
}
