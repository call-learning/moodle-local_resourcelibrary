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
 * Simple value select filter. A variant of the user_filter_simpleselect.
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

/**
 * Generic filter based on a list of values.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class multiselect_filter extends baseselect_filter {

    /**
     * Check if this is the right type for this handler
     *
     * @param \core_customfield\field_controller $field
     * @return bool
     * @throws \moodle_exception
     */
    public static function check_is_righttype(\core_customfield\field_controller $field) {
        return \local_resourcelibrary\locallib\utils::is_multiselect_installed()
                && $field instanceof \customfield_multiselect\field_controller;
    }
    /**
     * Adds controls specific to this filter in the form.
     *
     * @param \MoodleQuickForm $mform
     *
     * @throws \coding_exception
     */
    public function add_to_form(\MoodleQuickForm &$mform) {
        $choices = $this->_options;
        $elementname = $this->get_form_value_item_name();
        $mform->addElement('searchableselector',
            $elementname,
            $this->_label,
            $choices,
            ['multiple' => true]);
        $mform->setType($elementname, $this->get_param_type());
        base::add_to_form($mform);
    }

    /**
     * Return the expected param type for cleaning up the value.
     * @return mixed
     */
    public function get_param_type() {
        return PARAM_RAW;
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
        $likes = [
            (object) ['operator' => ' = :%s ', 'value' => '%s'],
            (object) ['operator' => ' LIKE(:%s)', 'value' => '%s,%%'],
            (object) ['operator' => ' LIKE(:%s)', 'value' => '%%,%s'],
            (object) ['operator' => ' LIKE(:%s)', 'value' => '%%,%s,%%'],
        ];
        $name = 'ex_multiselect' . $counter++;

        if (!isset($data)) {
            return [null, null];
        }
        $values = explode(',', $data);

        $paramcount = 0;
        $field = $this->get_sql_field_name();
        $comparisonstring = "";
        $comparisonparams = [];
        foreach ($values as $v) {
            foreach ($likes as $like) {
                $currentname = $name . '_' . $paramcount;
                $comparisonstring .= ($comparisonstring ? " OR " : " ") . $field . sprintf($like->operator, $currentname);
                $comparisonparams[$currentname] = sprintf($like->value, $v);
                $paramcount++;
            }
        }
        $comparisonarray = ["($comparisonstring)", $comparisonparams];

        return empty($values) ? [null, null] : $comparisonarray;
    }
}

