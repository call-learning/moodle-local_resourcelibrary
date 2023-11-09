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
 * Date filter. A variant of the user_filter_simpleselect.
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

use DateTime;

/**
 * Date filter
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class date_filter extends base {
    /**
     * Constructor
     *
     * @param \core_customfield\field_controller $field user table filed name
     * @throws \moodle_exception
     */
    public function __construct(\core_customfield\field_controller $field) {
        parent::__construct($field);
        $this->_operator = self::OPERATOR_GREATERTHAN;
    }

    /**
     * Check if this is the right type for this handler
     *
     * @param \core_customfield\field_controller $field
     * @return bool
     * @throws \moodle_exception
     */
    public static function check_is_righttype(\core_customfield\field_controller $field) {
        return $field instanceof \customfield_date\field_controller;
    }

    /**
     * Adds controls specific to this filter in the form.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_to_form(\MoodleQuickForm &$mform) {
        $elementname = $this->get_form_value_item_name();
        $mform->addElement('date_selector', $elementname, $this->_label, ['optional' => true]);
        $mform->setType($elementname, $this->get_param_type());
        parent::add_to_form($mform);
    }

    /**
     * Return the expected param type for cleaning up the value.
     * @return mixed
     */
    public function get_param_type() {
        return PARAM_INT;
    }


    /**
     * Retrieves data from the form data
     *
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata) {
        $field = $this->_name;

        if (array_key_exists($field, (array) $formdata) && $formdata->$field !== '') {
            return ['value' => (string) $formdata->$field];
        }

        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     *
     * @param array $data filter settings
     * @return array sql string and $params (or array (null, null) if no filter)
     */
    public function get_sql_filter($data) {
        static $counter = 0;
        $name = 'ex_date' . $counter++;

        $value = substr($data, 2);
        // The provided value is 1,day,month,year (1 is for enabled).
        $timestamp = DateTime::createFromFormat('j,m,Y', $value)->getTimestamp();
        $field = $this->get_sql_field_name();
        $sqloperator = '>';
        return empty($value) ? [null, null] : ["$field $sqloperator :$name", [$name => $timestamp]];
    }
}
