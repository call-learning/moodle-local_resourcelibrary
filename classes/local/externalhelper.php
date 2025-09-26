<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_resourcelibrary\local;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class externalhelper
 *
 * Helper class to build external api parameters and return structures.
 *
 * @package    local_resourcelibrary
 * @copyright  2025 CALL Learning 2025 - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class externalhelper {
    /**
     * Generic filter parameters (common to activities and courses)
     *
     * @param string $parentid
     * @param string $parentiddesc
     * @return external_function_parameters
     */
    public static function get_filter_generic_parameters($parentid, $parentiddesc) {
        return new external_function_parameters(
            [$parentid => new external_value(PARAM_INT, $parentiddesc),
                'filters' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type' => new external_value(
                                PARAM_ALPHANUM,
                                'Filter type as per customfield/fields/ type class or another value like
                                globalsearch, ...'
                            ),
                            'shortname' => new external_value(
                                PARAM_ALPHANUMEXT,
                                'Matching customfield shortname if it is a customfield filter',
                                VALUE_OPTIONAL
                            ),
                            'operator' => new external_value(
                                PARAM_INT,
                                'Filter option as per local_resourcelibrary\filters class option
                                (this will be EQUAL, CONTAINS, NOTEQUAL...'
                            ),
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
                            'column' => new external_value(
                                PARAM_ALPHANUM,
                                'Column name for the sorting'
                            ),
                            'order' => new external_value(
                                PARAM_ALPHA,
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
     * Get Sort option for the SQL query
     *
     * @param array $sortoptions
     * @param array $fields
     * @return string
     */
    public static function get_sort_options_sql($sortoptions, $fields) {
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
