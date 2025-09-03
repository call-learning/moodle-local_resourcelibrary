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

use core_course\external\course_module_summary_exporter;
use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_resourcelibrary\locallib\customfield_utils;
use local_resourcelibrary\locallib\externalhelper;
use moodle_url;

/**
 * Get filtered course content for the catalogue
 *
 * @copyright  2025 CALL Learning 2025 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    local_resourcelibrary
 */
class get_filtered_course_content extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return externalhelper::get_filter_generic_parameters('courseid', 'course id');
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
     */
    public static function execute($courseid, $filters = [], $limit = 0, $offset = 0, $sorting = []) {
        global $PAGE, $DB;
        // Validate parameters.

        $inparams = compact(['courseid', 'filters', 'limit', 'offset', 'sorting']);
        $params = self::validate_parameters(self::execute_parameters(), $inparams);

        $sqlparams = ['courseid' => $courseid];
        $sqlwhere = "e.course = :courseid AND m.visible = 1"; // Only activated modules.

        $modulefields = ['section'];
        $additionalfields = [];
        foreach ($modulefields as $mfield) {
            $additionalfields[] = "e.{$mfield} AS {$mfield}";
        }
        $additionalfields[] = "e.added AS timemodified"; // There is no modification time for course module.
        $additionalfields[] = "m.name AS fullname";
        $additionaljoins = ['LEFT JOIN {modules} m ON m.id = e.module'];
        $handler = \local_resourcelibrary\customfield\coursemodule_handler::create();
        $sortsql = externalhelper::get_sort_options_sql($sorting, array_keys($additionalfields));
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
                $additionamoduleinfo->timemodified = $DB->get_field($cm->modname, 'timemodified', ['id' => $cm->instance]);
                if ($cm->url) {
                    $additionamoduleinfo->viewurl = $cm->url->out_as_local_url();
                } else {
                    $additionamoduleinfo->viewurl = (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);
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
    public static function execute_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    [
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
                        'viewurl' => new external_value(PARAM_URL, 'The module URL'), ]
                    , 'course module'
                )
            );
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
}
