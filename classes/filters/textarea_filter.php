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
 * Simple textarea filter. A variant of the simpletext_filter.
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

/**
 * Generic filter based on a text content
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class textarea_filter extends basetext_filter {
    /**
     * Check if this is the right type for this handler
     *
     * @param \core_customfield\field_controller $field
     * @return bool
     * @throws \moodle_exception
     */
    public static function check_is_righttype(\core_customfield\field_controller $field) {
        return $field instanceof \customfield_textarea\field_controller;
    }
}
