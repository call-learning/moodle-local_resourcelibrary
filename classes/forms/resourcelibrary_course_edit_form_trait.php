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
 * Course Edit Form Trait : common routine for course edition
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\forms;

use context_coursecat;
use local_resourcelibrary\customfield\course_handler;

defined('MOODLE_INTERNAL') || die;

/**
 * The form for handling editing a course.
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait resourcelibrary_course_edit_form_trait {

    /**
     * Form definition.
     */
    public function add_definition() {
        $mform = $this->_form;
        $category = $this->_customdata['category'];
        $course = $this->_customdata['course'];

        $categorycontext = context_coursecat::instance($category->id);
        // Add custom fields to the form.
        $handler = course_handler::create();
        $handler->set_parent_context($categorycontext); // For course handler only.
        $handler->instance_form_definition($mform, empty($course->id) ? 0 : $course->id);
        // Prepare custom fields data.
        $handler->instance_form_before_set_data($course);
        // Finally set the current form data.
        $this->set_data($course);
        // Push the submit button at the end if it exists.
        if ($mform->elementExists('buttonar')) {
            $submitbuttons = $mform->removeElement('buttonar', false);
            $mform->addElement($submitbuttons);
        }
    }

    /**
     * Fill in the current page data for this course.
     */
    public function add_definition_after_data() {
        $mform = $this->_form;
        // Tweak the form with values provided by custom fields in use.
        $handler = \core_course\customfield\course_handler::create();
        $handler->instance_form_definition_after_data($mform, empty($courseid) ? 0 : $courseid);
    }

    /**
     * Validation.
     *
     * @param array $errors
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function add_validation($errors, $data, $files) {
        // Add the custom fields validation.
        $handler = \core_course\customfield\course_handler::create();
        return array_merge($errors, $handler->instance_form_validation($data, $files));
    }
}
