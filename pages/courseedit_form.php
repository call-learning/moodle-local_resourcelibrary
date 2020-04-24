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
 * Edit course settings form
 *
 * @package    core_course
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_resourcelibrary\forms\resourcelibrary_course_edit_form_trait;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
/**
 * A trait for handling course edition for
 */
class resourcelibrary_course_edit_form  extends moodleform {
    use resourcelibrary_course_edit_form_trait;
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $returnurl = $this->_customdata['returnurl'];

        $this->add_definition();

        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);
        // When two elements we need a group.
        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('savechangesanddisplay'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
    }

    /**
     * Fill in the current page data for this course.
     */
    public function definition_after_data() {
        $mform = $this->_form;
        // Tweak the form with values provided by custom fields in use.
        $handler = core_course\customfield\course_handler::create();
        $handler->instance_form_definition_after_data($mform, empty($courseid) ? 0 : $courseid);
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        // Add the custom fields validation.
        $handler = core_course\customfield\course_handler::create();
        return $handler->instance_form_validation($data, $files);
    }
}
