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
 * Internal function and routine for the plugin
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\locallib;
defined('MOODLE_INTERNAL') || die();

use core_customfield\handler;
use local_resourcelibrary\customfield\course_handler;

/**
 * Class utils
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * Check if multiselect installed
     * @return bool
     */
    public static function is_multiselect_installed() {
        return class_exists('\\customfield_multiselect\\field_controller');
    }

    /**
     * Full handler component name
     *
     * @param handler $handler
     * @return string
     */
    public static function get_handler_full_component($handler) {
        return $handler->get_component() . '_' . $handler->get_area();
    }

    /**
     * Simple function to get the filter config name for a handler
     *
     * @param handler $handler
     * @return string
     */
    public static function get_hidden_filter_config_name($handler) {
        return 'filter_hidden_' . static::get_handler_full_component($handler);
    }

    /**
     * Get hidden fields
     *
     * @param handler $handler
     * @return array
     * @throws \coding_exception
     */
    public static function get_hidden_fields_filters($handler) {
        $configname = static::get_hidden_filter_config_name($handler);
        $hiddenfieldslist =
            get_config('local_resourcelibrary', $configname);
        if (!$hiddenfieldslist) {
            return [];
        }
        return explode(',', $hiddenfieldslist);
    }

    /**
     * Check if given field is hidden
     *
     * @param handler $handler
     * @param string $fieldshortname
     * @throws \coding_exception
     */
    public static function is_field_hidden_filters($handler, $fieldshortname) {
        return in_array($fieldshortname, self::get_hidden_fields_filters($handler));
    }

    /**
     * Hide a field from filtering
     *
     * @param handler $handler
     * @param string|array $fieldshortname the field shortname or an array of fields shortnames
     * @throws \dml_exception
     */
    public static function hide_fields_filter($handler, $fieldshortname) {
        $hiddenfieldslist = self::get_hidden_fields_filters($handler);
        if (is_string($fieldshortname)) {
            $hiddenfieldslist[] = $fieldshortname;
        } else {
            if (is_array($fieldshortname)) {
                $hiddenfieldslist += $fieldshortname;
            }
        }
        $configname = static::get_hidden_filter_config_name($handler);
        set_config($configname, implode(',', $hiddenfieldslist), 'local_resourcelibrary');
    }

    /**
     * Show a field from filtering
     *
     * Removes it from the list of hidden fields if it is set.
     *
     * @param handler $handler
     * @param string|array $fieldshortname the field shortname or an array of fields shortnames
     * @throws \dml_exception
     */
    public static function show_fields_filter($handler, $fieldshortname) {
        $hiddenfieldslist = self::get_hidden_fields_filters($handler);
        $fieldstoremove = [];
        if (is_string($fieldshortname)) {
            $fieldstoremove[] = $fieldshortname;
        } else {
            if (is_array($fieldshortname)) {
                $fieldstoremove = $fieldshortname;
            }
        }
        $hiddenfieldslist = array_diff($hiddenfieldslist, $fieldstoremove);
        $configname = static::get_hidden_filter_config_name($handler);
        set_config($configname, implode(',', $hiddenfieldslist), 'local_resourcelibrary');
    }
}
