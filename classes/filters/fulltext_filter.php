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
 * Simple text filter. A variant of the simpletext_filter
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

/**
 * Generic fulltext filter for any entity.
 *
 * This is a "static" filter to be able to search through the entity text fields.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fulltext_filter implements resourcelibrary_filter_interface, static_filter_interface {

    /**
     * Add to form
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_to_form(\MoodleQuickForm &$mform) {
        $elementname = $this->get_form_value_item_name();
        $mform->addElement( 'text', 'fulltext', $this->get_label());
        $mform->setType($elementname, PARAM_TEXT);
        utils::add_filter_operators_to_form($mform,
            'fulltext',
            PARAM_TEXT,
            self::OPERATOR_EQUAL);

    }

    /**
     * Check data
     *
     * @param \stdClass $formdata
     * @return false|mixed|string[]
     */
    public function check_data($formdata) {
        $field = 'fulltext';
        if (array_key_exists($field, (array) $formdata) && $formdata->$field !== '') {
            return ['value' => (string) $formdata->$field];
        }
        return false;
    }

    /**
     * Get label
     *
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_label() {
        return get_string('fulltext', 'local_resourcelibrary');
    }

    /**
     * Get sql filter
     *
     * @param array $data
     * @return array|null[]
     */
    public function get_sql_filter($data) {
        global $DB;
        return empty($data) ? [null, null] : [
            $DB->sql_like('fullname', ":fulltext", false),
            ['fulltext' => "%$data%"], ];
    }
}
