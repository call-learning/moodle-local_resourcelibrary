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
 * Course resourcelibrary
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;

defined('MOODLE_INTERNAL') || die();

use core_customfield\field_controller;
use local_resourcelibrary\filters\customfield_utils;
use renderable;
use renderer_base;
use templatable;

/**
 * Class to display a filter.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter {

    /**
     * Associated field
     *
     * @var field_controller|null
     */
    protected $field = null;

    /**
     * Main constructor.
     * Initialize the user preferences
     * @param field_controller $field
     * @throws \dml_exception
     */
    public function __construct(field_controller $field) {
        $this->field = $field;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        $filterclass = '\local_resourcelibrary\filters\\' . $this->field->get('type') . '_filter';
        $data = [];
        if (class_exists($filterclass)) {
            $name = customfield_utils::get_field_name('customfield_', $this->field->get('shortname'));
            $filter = new $filterclass($name, $this->field->get('name'), $this->field);
            $data['filtername'] = $this->field->get('name');
            $data['filterformelement'] = $filter->get_form_element()->export_for_template($output);
        }
        return $data;
    }

}