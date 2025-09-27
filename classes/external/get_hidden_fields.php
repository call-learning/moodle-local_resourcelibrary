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

use coding_exception;
use core_customfield\handler;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use invalid_parameter_exception;
use local_resourcelibrary\local\utils;
use moodle_exception;

/**
 * Class used for Ajax Management of the custom field (administration)
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_hidden_fields extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'component' => new external_value(
                    PARAM_ALPHANUMEXT,
                    'customfield handler type course'
                ),
                'area' => new external_value(
                    PARAM_ALPHANUMEXT,
                    'customfield handler area'
                ),
            ]
        );
    }

    /**
     * Get the fields shortnames that are marked as hidden
     *
     * @param string $component
     * @param string $area
     * @return mixed
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function execute(string $component, string $area) {
        // Validate parameters.
        $inparams = compact(['component', 'area']);
        self::validate_parameters(self::execute_parameters(), $inparams);
        $handler = handler::get_handler($component, $area);
        $hiddenfields = utils::get_hidden_fields_filters($handler);
        return array_map(
            function ($shortname) {
                return ['shortname' => $shortname];
            },
            $hiddenfields
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_multiple_structure
     * @since Moodle 2.2
     */
    public static function execute_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    [
                        'shortname' => new external_value(PARAM_ALPHANUM, 'field shortname'),
                    ]
                )
            );
    }
}
