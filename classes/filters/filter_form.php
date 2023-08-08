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
 * This file contains forms used to filter user.
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\filters;

use local_resourcelibrary\locallib\customfield_utils;
use local_resourcelibrary\locallib\utils;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Generic filter form.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform =& $this->_form;
        $handler = $this->_customdata['handler'];
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('filters', 'local_resourcelibrary'));
        $mform->setAdvanced('miscellaneoussettingshdr');
        foreach ($handler->get_fields() as $field) {
            if (!utils::is_field_hidden_filters($handler, $field->get('shortname'))) {
                $filter = customfield_utils::get_filter_from_field($field);
                if ($filter) {
                    $filter->add_to_form($mform);
                }
            }
        }
        // Add button.

        $buttonarray[] = &$mform->createElement('submit', 'filterbutton',
            get_string('filter:submit', 'local_resourcelibrary'));
        $buttonarray[] = $mform->createElement('submit', 'resetbutton', get_string('clear'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Retrieve values passed as GET (to go directly to the search page)
     */
    public function after_definition() {
        global $_GET;
        parent::after_definition();
        // Get custom field presets.
        $submission = $_GET;
        merge_query_params($submission, $_POST);
        $prefilters = [];

        // Filter out non relevant values.
        $handler = $this->_customdata['handler'];
        foreach ($handler->get_fields() as $field) {
            $shortname = $field->get('shortname');
            if (!utils::is_field_hidden_filters($handler, $shortname)) {
                $filter = customfield_utils::get_filter_from_field($field);
                foreach ($submission as $key => $value) {
                    if ($key == 'customfield_' . $shortname) {
                        $prefilters[$key]['operator'] = clean_param($value['operator'], PARAM_INT);
                        $prefilters[$key]['type'] = clean_param($value['type'], PARAM_ALPHANUMEXT);
                        if (is_array($value['value'])) {
                            foreach ($value['value'] as $k => $v) {
                                $prefilters[$key]['value'][$k] = clean_param($v, $filter->get_param_type());
                            }
                        } else {
                            $prefilters[$key]['value'] = clean_param($value['value'], $filter->get_param_type());
                        }
                    }
                }
            }
        }
        // Set predefined values.
        $this->_form->updateSubmission($prefilters, null);
    }
}
