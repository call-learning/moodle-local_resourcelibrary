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
 * Resource Library Filter Interface
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\locallib;

use core_customfield\field_controller;
use core_customfield\handler;
use local_resourcelibrary\locallib\utils;

/**
 * Class customfield_utils
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfield_utils {
    /**
     * Get SQL query for given search/filters
     *
     * @param string $type
     * @param array $additionaljoins a list of joins in the textual form, they can reference the entity by e.id
     * example 'LEFT JOIN {course_categories} cat ON c.id = e.category'. Note that it should only be one to one relationship
     * No check is done and it can create an erroneous query. This will be placed after the FROM ... and before the
     * custom field joins.
     * @param array $additionalfields a list of fields to add to the select returned values. Best is to alias them to
     * make sure that there is no unwanted side effect. Example for a course, 'e.fullname as fullname' or if there
     * is any join you can also reference them. For example 'cat.name AS categoryname'. No check is done here
     * so it can create a erroneous query.
     * This will be placed before the join fields and after the id field
     * @return string  a sql query with all columns from custom field (all prefixed by 'customfield'. Note that this will produce
     * the raw value of the field and not the displayable version
     * @throws \ReflectionException
     * @throws \moodle_exception
     */
    public static function get_sql_for_entity_customfields($type, $additionaljoins = [], $additionalfields = []) {
        $table = 'course';
        $customfieldhandler = \core_course\customfield\course_handler::create();
        list($customfields, $customjoins) = self::get_fields_and_joins_for_cf_handler(
            $customfieldhandler,
            'course');
        $joinsfields = array_values(array_merge($additionalfields, $customfields));
        $joins = array_values(array_merge($additionaljoins, $customjoins));

        $sql = "SELECT e.id as id, " . implode(', ', $joinsfields) . " FROM {{$table}} e " . implode(' ', $joins);

        return $sql;
    }

    /**
     * Get all the custom fields of this type (handler) in a joined column in the form of $prefix_'shortname'
     * Note: We have an assumption here : we the shortname of the custom field is unique across all custom fields
     * of the same area
     *
     * @param handler $handler A customfield handler
     * @param string $table relevant table
     * @param string $prefix This will be always 'customfield' so to match forms fields (which cannot be changed
     * due to the fact the prefix is defined in the customfield type)
     * @return array[]
     * @throws \ReflectionException
     * @throws \moodle_exception When the shortname is not unique
     */
    public static function get_fields_and_joins_for_cf_handler($handler, $table, $prefix = 'customfield') {
        $joins = [];
        $joinsfields = [];
        foreach ($handler->get_fields() as $f) {
            if (!utils::is_field_hidden_filters($handler, $f->get('shortname'))) {
                $id = $f->get('id');
                $datafieldname = self::get_field_name($prefix, $f->get('shortname'));
                $joins[$datafieldname] = "LEFT JOIN {customfield_data} {$prefix}_{$id}
                ON e.id = {$prefix}_{$id}.instanceid AND {$prefix}_{$id}.fieldid = $id";
                $datafieldcolumn = self::get_datafieldcolumn_value_from_field_handler($f);
                $joinfield = "{$prefix}_{$id}." . $datafieldcolumn . " AS {$datafieldname}";
                if (!empty($joinsfields[$datafieldname])) {
                    throw new \moodle_exception('shortnameshouldbeunique', 'local_resourcelibrary');
                }
                $joinsfields[$datafieldname] = $joinfield;
            }
        }
        return [$joinsfields, $joins];
    }

    /**
     * From a field, get the corresponding column in the data table
     *
     * @param field_controller $field
     * @return string
     * @throws \ReflectionException
     */
    public static function get_datafieldcolumn_value_from_field_handler($field) {
        // HACK : Here a bit of dark magic to avoid the if/switch.
        // Ideally we should be able to get this information directly and in a more traditional way
        // (such as f->datafield() that would delegate to the datafield).
        $fieldclass = new \ReflectionClass($field);
        /* @var $datafield \core_customfield\data_controller The corresponding datafield. */
        $dataclass = new \ReflectionClass($fieldclass->getNamespaceName() . '\data_controller');
        $datafield = $dataclass->newInstanceWithoutConstructor();
        // TODO: MDL-0 here we should have access to get_form_element_name from the datacontroller.
        return $datafield->datafield();
    }

    /**
     * Get field name
     *
     * @param string $prefix
     * @param string $shortname
     * @return string
     */
    public static function get_field_name($prefix, $shortname) {
        return "{$prefix}_" . trim(strtolower($shortname));
    }

    /**
     * Find filter from field
     *
     * @param field_controller $field
     * @return mixed|null
     */
    public static function get_filter_from_field($field) {
        $filterclass = \local_resourcelibrary\filters\utils::get_first_matching_filter($field);
        if (class_exists($filterclass)) {
            $filter = new $filterclass($field);
            return $filter;
        }
        return null;
    }

    /**
     * Get the where/params for the matching sql query
     *
     * @param array $filters
     * @param field_controller $handler
     * @return array
     */
    public static function get_sql_from_filters_handler($filters, $handler) {
        // Add custom field filters to the query.
        $sqlwhere = "";
        $sqlparams = [];
        $allfields = $handler->get_fields();
        foreach ($filters as $filter) {
            // Check if the field is marked as hidden for filters.
            if (!utils::is_field_hidden_filters($handler, $filter['shortname'])) {
                if (!empty($filter['shortname'])) {
                    $matchingfields = array_filter($allfields,
                        function($f) use ($filter) {
                            return strtolower($f->get('shortname')) == strtolower($filter['shortname']);
                        });
                    if ($matchingfields) {
                        $matchingfield = reset($matchingfields);
                        $f = self::get_filter_from_field($matchingfield);
                        list($where, $params) = $f->get_sql_filter($filter['value']);
                        if ($where) {
                            $sqlwhere .= " AND $where ";
                            $sqlparams += $params;
                        }
                    }
                }
            }
        }
        return [$sqlwhere, $sqlparams];
    }

    /**
     * Get records from field handler
     *
     * @param handler $handler
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param array $additionaljoins
     * @param array $additionalfields
     * @param array $additionalwheres
     * @param array $additionalparams
     * @param array $additionalsorts
     * @return array
     * @throws \ReflectionException
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_records_from_handler(handler $handler,
        $filters,
        $limit,
        $offset,
        $additionaljoins,
        $additionalfields,
        $additionalwheres,
        $additionalparams,
        $additionalsorts
    ) {
        global $DB;
        $sqlwhere = "WHERE 1=1 AND " . $additionalwheres;
        $sqlparams = $additionalparams;
        $sqlorderby = $additionalsorts ? "ORDER BY $additionalsorts" : "";
        // Courses or modules which are invisible are last (to avoid gaps in pagination).

        list($sqlwherefilter, $sqlparamsfilters) = self::get_sql_from_filters_handler($filters, $handler);
        $sqlwhere .= $sqlwherefilter;
        $sqlparams += $sqlparamsfilters;
        $sqllimit = $limit ? "LIMIT {$limit} OFFSET {$offset}" : "";

        $sql = self::get_sql_for_entity_customfields(
            $handler->get_area(),
            $additionaljoins,
            $additionalfields
        );
        return $DB->get_records_sql("{$sql}  {$sqlwhere} {$sqlorderby} {$sqllimit}", $sqlparams);
    }
}
