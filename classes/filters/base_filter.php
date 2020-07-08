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
 * Base filter.
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

defined('MOODLE_INTERNAL') || die;

/**
 * Generic base filter for all other filters.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_filter implements resourcelibrary_filter_interface {

    /**
     * Name of the filter in the form
     *
     * @var string
     */
    protected $_name;

    /**
     * label of the filter in the form
     *
     * @var string
     */
    protected $_label;

    /**
     * @var \core_customfield\field_controller
     */
    protected $_field;

    /**
     * Current operator
     *
     * @var int
     */
    protected $_operator;

    /**
     * Constructor
     *
     * @param \core_customfield\field_controller $field user table filed name
     */
    public function __construct(\core_customfield\field_controller $field) {
        $this->_name = customfield_utils::get_field_name('customfield', $field->get('shortname'));
        $this->_operator = self::OPERATOR_EQUAL; // Equal by default.
        $this->_label = $field->get_formatted_name();
        $this->_field = $field;
    }

    /**
     * Adds controls specific to this filter in the form.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_to_form(\MoodleQuickForm &$mform) {
        $typename = $this->_name . '[type]';
        $operatorname = $this->_name . '[operator]';
        $mform->addElement('hidden', $typename, $this->_field->get('type'));
        $mform->setType($typename, PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', $operatorname, $this->_operator);
        $mform->setType($operatorname, PARAM_INT);
        $opinstructions = '';
        switch ($this->_operator) {
            case self::OPERATOR_LESSTHAN:
                $opinstructions = 'lessthan';
                break;
            case self::OPERATOR_GREATERTHAN:
                $opinstructions = 'greaterthan';
                break;
            case self::OPERATOR_EMPTY:
                $opinstructions = 'empty';
                break;
            case self::OPERATOR_NOTEMPTY:
                $opinstructions = 'notempty';
                break;
        }
        if ($opinstructions) {
            $mform->addElement('static',
                $this->_name . 'instructions',
                \html_writer::span("(".
                    get_string('operator:instructions:' . $opinstructions, 'local_resourcelibrary')
                    ."*)",
                    'filter-instructions')
            );
        }
    }

    /**
     * Get the name of the item that will store the value
     *
     * @return string
     */
    protected function get_form_value_item_name() {
        return $this->_name . '[value]';
    }

    /**
     * Retrieves data from the form data
     *
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public abstract function check_data($formdata);

    /**
     * Returns the condition to be used with SQL where
     *
     * @param array $data filter settings
     * @return array sql string and $params
     */
    public abstract function get_sql_filter($data);

    /**
     * Get field name
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function get_sql_field_name() {
        $datafieldcolumn = customfield_utils::get_datafieldcolumn_value_from_field_handler($this->_field);
        $fieldid = $this->_field->get('id');
        return "customfield_{$fieldid}.{$datafieldcolumn}";
    }

    /**
     * Returns a human friendly description of the filter used as label.
     *
     * @return string active filter label
     */
    public function get_label() {
        return $this->_label;
    }
}

