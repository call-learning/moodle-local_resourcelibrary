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
 * @package local_resourcelibrary
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_resourcelibrary\forms\resourcelibrary_course_edit_form_trait;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/edit_form.php');

/**
 * The form for handling editing a course.
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editpage_resourcelibrary_course_edit_form extends course_edit_form {
    use resourcelibrary_course_edit_form_trait;

    /**
     * Form definition.
     */
    public function definition() {
        parent::definition();
        $this->add_definition();
    }

    /**
     * Fill in the current page data for this course.
     */
    public function definition_after_data() {
        parent::definition_after_data();
        $this->add_definition_after_data();
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        return $this->add_validation(parent::validation($data, $files), $data, $files);
    }
}
