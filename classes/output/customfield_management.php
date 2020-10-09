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
 * Custom field management
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;
defined('MOODLE_INTERNAL') || die();

use core_customfield\output\management;
use local_resourcelibrary\locallib\utils;

/**
 *  Custom field management
 *
 *  Same as usual custom field management but adds further options so
 *  we can hide the field from the filter, ...
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfield_management extends management {
    /**
     * Export for template
     *
     * @param \renderer_base $output
     * @return array|object|\stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = parent::export_for_template($output);

        foreach ($data->categories as &$category) {
            foreach ($category['fields'] as &$field) {
                $field['hiddenstatus'] = utils::is_field_hidden_filters($this->handler, $field['shortname']) ? 'checked' : '';
            }
        }
        $data->handlercomponent = $this->handler->get_component();
        $data->handlerarea = $this->handler->get_area();
        return $data;
    }
}