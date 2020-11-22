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

namespace local_resourcelibrary\filters;

defined('MOODLE_INTERNAL') || die;

/**
 * Interface to be implemented by all Resource Library Filters
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface resourcelibrary_filter_interface {

    /**
     * Returns the condition to be used with SQL where
     *
     * @param array $data filter settings
     * @return array ($wherestring,$params) or array (null, null) if filter disabled
     */
    public function get_sql_filter($data);

    /**
     * Retrieves data from the form data
     *
     * @param \stdClass $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata);

    /**
     * Adds controls specific to this filter in the form.
     * @param \MoodleQuickForm $mform
     *
     */
    public function add_to_form(\MoodleQuickForm &$mform);

    /**
     * Returns a human friendly description of the filter used as label.
     *
     * @return string active filter label
     */
    public function get_label();

    /**
     * Operator: Equals
     */
    const OPERATOR_EQUAL = 1;
    /**
     * Operator: Not Equals - not used.
     */
    const OPERATOR_NOT_EQUAL = 2;
    /**
     * Operator: Contains - not used.
     */
    const OPERATOR_CONTAINS = 3;
    /**
     * Operator: Does not contain - not used.
     */
    const OPERATOR_DOESNOTCONTAIN = 4;
    /**
     * Operator: Empty.
     */
    const OPERATOR_EMPTY = 5;
    /**
     * Operator: Not empty.
     */
    const OPERATOR_NOTEMPTY = 6;
    /**
     * Operator: Less than.
     */
    const OPERATOR_LESSTHAN = 7;
    /**
     * Operator: Greater than.
     */
    const OPERATOR_GREATERTHAN = 8;

}
