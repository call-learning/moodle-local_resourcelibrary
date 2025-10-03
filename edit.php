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
 * Edit catalogue page
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use local_resourcelibrary\local\persistent\catalogue_page;

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$returnurl = new moodle_url('/local/resourcelibrary/catalogue_pages.php');

require_login();
require_capability('local/resourcelibrary:managecatalogues', context_system::instance());

if ($action === 'delete' && $id > 0) {
    require_sesskey();
    $page = new catalogue_page($id);
    $page->delete();
    redirect($returnurl, get_string('deleted'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$page = null;
if ($id > 0) {
    $page = new catalogue_page($id);
    $title = get_string('editcataloguepage', 'local_resourcelibrary');
} else {
    $title = get_string('addcataloguepage', 'local_resourcelibrary');
}

$PAGE->set_url('/local/resourcelibrary/edit.php', ['id' => $id]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('cataloguepages', 'local_resourcelibrary'), $returnurl);
$PAGE->navbar->add($title);

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for editing catalogue pages
 */
class catalogue_page_form extends moodleform {

    /**
     * Define the form
     */
    public function definition() {
        $mform = $this->_form;
        $page = $this->_customdata['page'] ?? null;

        // Name field
        $mform->addElement('text', 'name', get_string('name'), 'maxlength="255" size="60"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Course categories selection
        $options = [];
        $categories = core_course_category::get_all();
        foreach ($categories as $category) {
            $options[$category->id] = $category->get_formatted_name();
        }

        $select = $mform->addElement('autocomplete', 'categories', get_string('categories'), $options);
        $select->setMultiple(true);
        $mform->addHelpButton('categories', 'categories', 'local_resourcelibrary');

        // Custom fields selection
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_fields();

        $customfieldoptions = [];
        foreach ($customfields as $field) {
            $customfieldoptions[$field->get('shortname')] = $field->get('name');
        }

        if (!empty($customfieldoptions)) {
            $select = $mform->addElement('autocomplete', 'customfields', get_string('customfields', 'local_resourcelibrary'), $customfieldoptions);
            $select->setMultiple(true);
            $mform->addHelpButton('customfields', 'customfields', 'local_resourcelibrary');
        }

        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Action buttons
        $this->add_action_buttons();
    }

    /**
     * Validate the form data
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty(trim($data['name']))) {
            $errors['name'] = get_string('required');
        }

        return $errors;
    }
}

$form = new catalogue_page_form(null, ['page' => $page]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {

    if ($page) {
        // Update existing page
        $page->set('name', $data->name);
        $page->set_categories_array($data->categories ?? []);
        $page->set_customfields_array($data->customfields ?? []);
        $page->update();
        $message = get_string('cataloguepageupdated', 'local_resourcelibrary');
    } else {
        // Create new page
        $page = new catalogue_page();
        $page->set('name', $data->name);
        $page->set_categories_array($data->categories ?? []);
        $page->set_customfields_array($data->customfields ?? []);
        $page->create();
        $message = get_string('cataloguepagecreated', 'local_resourcelibrary');
    }

    redirect($returnurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

// Set form data
if ($page) {
    $formdata = [
        'id' => $page->get('id'),
        'name' => $page->get('name'),
        'categories' => $page->get_categories_array(),
        'customfields' => $page->get_customfields_array(),
    ];
    $form->set_data($formdata);
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
