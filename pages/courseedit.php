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
 * Edit course settings
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
global $CFG;
require_once($CFG->dirroot . '/course/lib.php');
require_once(dirname(__FILE__) . '/courseedit_form.php');

$id = required_param('id', PARAM_INT); // Course id.
$returnurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $id));

$PAGE->set_pagelayout('admin');
$pageparams = array('id' => $id);
$PAGE->set_url('/local/resourcelibrary/pages/courseedit.php', $pageparams);

// Basic access control checks.
// Editing course.
if ($id == SITEID) {
    // Don't allow editing of  'site course' using this from.
    print_error('cannoteditsiteform');
}

// Login to the course and retrieve also all fields defined by course format.
$course = get_course($id);
require_login($course);
$course = course_get_format($course)->get_course();

$category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);
$coursecontext = context_course::instance($course->id);
require_capability('local/resourcelibrary:editvalue', $coursecontext);

// First create the form.
$args = array(
    'course' => $course,
    'category' => $category,
    'returnurl' => $returnurl
);
$editform = new resourcelibrary_course_edit_form(null, $args);
if ($editform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    // Process data if submitted.

    // Save any changes to the files used in the editor.
    update_course($data);

    // Update custom fields if there are any of them in the form.
    \local_resourcelibrary\locallib\utils::course_update_fields($data);

    // Set the URL to take them too if they choose save and display.
    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
    // Save and return. Take them back to wherever.
    redirect($returnurl);
}

// Print the form.

$site = get_site();

$streditcoursesettings = get_string("resourcelibraryfieldsettings", 'local_resourcelibrary');

// Navigation note: The user is editing a course, the course will exist within the navigation and settings.
// The navigation will automatically find the Edit settings page under course navigation.
$pagedesc = $streditcoursesettings;
$title = $streditcoursesettings;
$fullname = $course->fullname;

$PAGE->set_title($title);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($pagedesc);

$editform->display();

echo $OUTPUT->footer();
