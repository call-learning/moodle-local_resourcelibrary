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
use Matrix\Exception;

/**
 * Class utils
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * Get Resource library URL and text description for the current page
     *
     * @param null $page
     *
     * @return array an array containing a text and the url to the catalog page
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_catalog_url($page = null) {
        global $CFG, $PAGE;
        if (!$page) {
            $page = $PAGE;
        }
        if ($page->context) {
            $context = $page->context;
        } else {
            $context = \context_system::instance();
        }
        $urltext = static::get_resource_library_menu_text();
        $params = [];
        $activities = get_config('local_resourcelibrary', 'activateactivitylibrary');
        if ($context instanceof \context_course && $activities) {
            global $DB;
            $params['courseid'] = $context->instanceid;
            $coursename = $DB->get_field('course', 'shortname', array('id' => $context->instanceid));
            $urltext = static::get_resource_library_menu_text($coursename);
        }
        return [
            $urltext,
            new \moodle_url($CFG->wwwroot . '/local/resourcelibrary/index.php', $params)];
    }

    /**
     * Check if multiselect installed
     *
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
        static $hiddenfields = null;
        if ($hiddenfields) {
            return $hiddenfields;
        }
        $configname = static::get_hidden_filter_config_name($handler);
        $hiddenfieldslist =
            get_config('local_resourcelibrary', $configname);
        if (!$hiddenfieldslist) {
            return [];
        }
        $hiddenfields = explode(',', $hiddenfieldslist);
        return $hiddenfields;
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
                $hiddenfieldslist = array_merge($hiddenfieldslist, $fieldshortname);
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

    /**
     * Global function to get the ressource library link/menu text.

     * This allow to override the menu in other plugin or just by adjusting
     * this setting.
     * The usual language string is returned if the setting is left empty.
     *
     * @param string $coursename
     * @return false|\lang_string|mixed|object|string|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_resource_library_menu_text($coursename = "") {
        $rsmenutext = get_config('local_resourcelibrary', 'menutextoverride');
        $generictext = get_string('resourcelibrary', 'local_resourcelibrary');
        $currentlang = current_language();
        $courseref = "";

        if ($coursename) {
            $courseref = " ({$coursename})";
        }
        if (!trim($rsmenutext)) {
            return $generictext . $courseref;
        }
        try {
            $alllangs = array_map(
                function($value) {
                    return explode('|', $value);
                },
                explode('\n', $rsmenutext)
            );

            foreach ($alllangs as $lang) {
                if ($lang && !empty($lang[1] && $lang[1] == $currentlang)) {
                    return $lang[0] . $courseref;
                }
            }
            return $generictext . $courseref;
        } catch (Exception $e) {
            return $generictext . $courseref;
        }
    }
}
