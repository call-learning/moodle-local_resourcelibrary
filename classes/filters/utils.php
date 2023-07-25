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
 * Filter utils.
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

use ReflectionClass;

/**
 * Class utils
 *
 * Generic function to find the filter plugins.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Get all syllabus display classes
     */
    public static function get_all_filters_classes() {
        $classes = [];
        foreach (\core_component::get_plugin_types() as $type => $location) {
            $plugins = \core_component::get_plugin_list($type);
            foreach (array_keys($plugins) as $name) {
                $locationtoscan = "{$location}/{$name}/classes/filters";
                if (is_dir($locationtoscan)) {
                    $sources = scandir($locationtoscan);
                    foreach ($sources as $filename) {
                        if ($filename === 'base.php' || $filename === "." || $filename === "..") {
                            continue;
                        }
                        $sourcename = str_replace('.php', '', $filename);
                        $classname = "\\{$type}_{$name}\\filters\\{$sourcename}";
                        if (class_exists($classname)) {
                            $reflector = new ReflectionClass($classname);
                            if ($reflector->isSubclassOf(\local_resourcelibrary\filters\base::class)) {
                                $classes[$sourcename] = $classname;
                            }
                        }
                    }
                }
            }
        }
        return $classes;
    }

    /**
     * Get matching class. Send a debug message if several similar class
     * matches the same field type.
     *
     * In general we will take the one out of the local_resourcelibrary namespace
     * as having the most priority.
     *
     * @param \core_customfield\field_controller $field
     * @return mixed
     */
    public static function get_first_matching_filter(\core_customfield\field_controller $field) {
        static $allfilterclasses = null; // We cache this in subsequent calls.

        if (!$allfilterclasses) {
            $allfilterclasses = static::get_all_filters_classes();
        }
        $externalfilters = [];
        $rootfilters = [];
        foreach ($allfilterclasses as $filtersource => $filterclass) {
            if ($filterclass::check_is_righttype($field)) {
                if (strpos("\\local_resourcelibrary\\filters", $filterclass) == 0) {
                    $rootfilters[] = $filterclass;
                } else {
                    $externalfilters[] = $filterclass;
                }
            }
        }
        // Redefined versions of filter have higher priority.
        if (count($externalfilters) > 1) {
            debugging('There are two filters matching this field type'
                . join(',', $externalfilters), DEBUG_NORMAL);
        }
        if (count($rootfilters) > 1) {
            debugging('There are two filters matching this field type'
                . join(',', $rootfilters), DEBUG_NORMAL);
        }
        if ($externalfilters) {
            return reset($externalfilters);
        }
        return reset($rootfilters);
    }

    /**
     * Add filter operator to form
     *
     * @param \moodleform $mform
     * @param string $name
     * @param string $type
     * @param int $operator
     * @throws \coding_exception
     */
    public static function add_filter_operators_to_form(&$mform,
        $name,
        $type,
        $operator) {
        $typename = $name . '[type]';
        $operatorname = $name . '[operator]';
        $mform->addElement('hidden', $typename, $type);
        $mform->setType($typename, PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', $operatorname, $operator);
        $mform->setType($operatorname, PARAM_INT);
        $opinstructions = '';
        switch ($operator) {
            case resourcelibrary_filter_interface::OPERATOR_LESSTHAN:
                $opinstructions = 'lessthan';
                break;
            case resourcelibrary_filter_interface::OPERATOR_GREATERTHAN:
                $opinstructions = 'greaterthan';
                break;
            case resourcelibrary_filter_interface::OPERATOR_EMPTY:
                $opinstructions = 'empty';
                break;
            case resourcelibrary_filter_interface::OPERATOR_NOTEMPTY:
                $opinstructions = 'notempty';
                break;
        }
        if ($opinstructions) {
            $mform->addElement('static',
                $name . 'instructions',
                \html_writer::span("(" .
                    get_string('operator:instructions:' . $opinstructions, 'local_resourcelibrary')
                    . "*)",
                    'filter-instructions')
            );
        }
    }
}
