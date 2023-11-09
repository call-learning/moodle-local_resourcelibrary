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
 * Course and Activity Custom field manager
 *
 * Remove any non relevant information to speedup the rendering
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\external;

use core_customfield\handler;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_resourcelibrary\locallib\utils;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * Class used for Ajax Management of the custom field (administration)
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_customfields extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_hidden_fields_filter_parameters() {
        return new external_function_parameters(
            [
                'component' => new external_value(PARAM_ALPHANUMEXT,
                    'customfield handler type (course, coursemodule)'),
                'area' => new external_value(PARAM_ALPHANUMEXT,
                    'customfield handler area'),
            ]
        );
    }

    /**
     * Get the fields shortnames that are marked as hidden
     *
     * @param string $component
     * @param string $area
     * @return mixed
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function get_hidden_fields_filter(string $component, string $area) {
        // Validate parameters.
        $inparams = compact(['component', 'area']);
        self::validate_parameters(self::get_hidden_fields_filter_parameters(), $inparams);
        $handler = handler::get_handler($component, $area);
        return utils::get_hidden_fields_filters($handler);
    }

    /**
     * Returns description of method result value
     *
     * @return external_multiple_structure
     * @since Moodle 2.2
     */
    public static function get_hidden_fields_filter_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    [
                        'shortname' => new external_value(PARAM_ALPHANUM, 'field shortname'),
                    ]
                )
            );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function hide_fields_filter_parameters() {
        return new external_function_parameters(
            [
                'component' => new external_value(PARAM_ALPHANUMEXT,
                    'customfield handler type (course, coursemodule)'),
                'area' => new external_value(PARAM_ALPHANUMEXT,
                    'customfield handler area'),
                'fieldshortnames' => new external_multiple_structure(
                    new external_value(PARAM_ALPHANUMEXT, 'ccustomfield shortname')
                ),
            ]
        );
    }

    /**
     * Hide a field in the filter list
     *
     * @param string $component
     * @param string $area
     * @param array $fieldshortnames
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function hide_fields_filter($component, $area, $fieldshortnames) {
        // Validate parameters.
        $inparams = compact(['component', 'area', 'fieldshortnames']);
        self::validate_parameters(self::hide_fields_filter_parameters(), $inparams);
        $handler = handler::get_handler($component, $area);
        utils::hide_fields_filter($handler, $fieldshortnames);
    }

    /**
     * Returns description of method result value
     *
     * @return external_multiple_structure
     * @since Moodle 2.2
     */
    public static function hide_fields_filter_returns() {
        return null;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function show_fields_filter_parameters() {
        return new external_function_parameters(
            [
                'component' => new external_value(PARAM_ALPHANUMEXT,
                    'customfield handler type (course, coursemodule)'),
                'area' => new external_value(PARAM_ALPHANUMEXT,
                    'customfield handler area'),
                'fieldshortnames' => new external_multiple_structure(
                    new external_value(PARAM_ALPHANUMEXT, 'ccustomfield shortname')
                ),
            ]
        );
    }

    /**
     * Show a field in the filter list
     *
     * @param string $component
     * @param string $area
     * @param array $fieldshortnames
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function show_fields_filter($component, $area, $fieldshortnames) {
        // Validate parameters.
        $inparams = compact(['component', 'area', 'fieldshortnames']);
        self::validate_parameters(self::show_fields_filter_parameters(), $inparams);
        $handler = handler::get_handler($component, $area);
        utils::show_fields_filter($handler, $fieldshortnames);
    }

    /**
     * Returns description of method result value
     *
     * @return external_multiple_structure
     * @since Moodle 2.2
     */
    public static function show_fields_filter_returns() {
        return null;
    }

}
