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
 * Activity handler for metadata fields
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\customfield;

defined('MOODLE_INTERNAL') || die;

use core_customfield\handler;
use local_resourcelibrary\common_cf_handler;
use core_customfield\field_controller;
use restore_activity_task;

/**
 * Course handler for custom fields
 *
 * @package local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemodule_handler extends handler {

    use common_cf_handler;

    /** @var int Field is displayed in the course listing, visible to everybody */
    const VISIBLETOALL = 2;
    /** @var int Field is displayed in the course listing but only for teachers */
    const VISIBLETOTEACHERS = 1;
    /** @var int Field is not displayed in the course listing */
    const NOTVISIBLE = 0;

    /**
     * Allows to add custom controls to the field configuration form that will be saved in configdata
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header',
            'resourcelibrary_coursemodule_handler_header',
            get_string('resourcelibraryfieldsettings', 'local_resourcelibrary'));
        $mform->setExpanded('resourcelibrary_coursemodule_handler_header', true);

        // If field is locked.
        $mform->addElement('selectyesno',
            'configdata[locked]',
            get_string('resourcelibraryfield_islocked', 'local_resourcelibrary'));
        $mform->addHelpButton('configdata[locked]', 'resourcelibraryfield_islocked', 'local_resourcelibrary');

        // Field data visibility.
        $visibilityoptions = [self::VISIBLETOALL =>
            get_string('resourcelibraryfield_visibletoall', 'local_resourcelibrary'),
            self::VISIBLETOTEACHERS =>
                get_string('resourcelibraryfield_visibletoteachers', 'local_resourcelibrary'),
            self::NOTVISIBLE =>
                get_string('resourcelibraryfield_notvisible', 'local_resourcelibrary')];
        $mform->addElement('select',
            'configdata[visibility]',
            get_string('resourcelibraryfield_visibility', 'local_resourcelibrary'),
            $visibilityoptions);
        $mform->addHelpButton(
            'configdata[visibility]', 'resourcelibraryfield_visibility', 'local_resourcelibrary');
    }

    /**
     * Creates or updates custom field data.
     *
     * @param \restore_task $task
     * @param array $data
     */
    public function restore_instance_data_from_backup(\restore_task $task, array $data) {
        /* @var $task restore_activity_task The current restore task class */
        $moduleid = $task->get_moduleid();
        $context = $this->get_instance_context($moduleid);
        $editablefields = $this->get_editable_fields($moduleid);
        $records = api::get_instance_fields_data($editablefields, $moduleid);
        $target = $task->get_target();
        $override = ($target != \backup::TARGET_CURRENT_ADDING && $target != \backup::TARGET_EXISTING_ADDING);

        foreach ($records as $d) {
            $field = $d->get_field();
            if ($field->get('shortname') === $data['shortname'] && $field->get('type') === $data['type']) {
                if (!$d->get('id') || $override) {
                    $d->set($d->datafield(), $data['value']);
                    $d->set('value', $data['value']);
                    $d->set('valueformat', $data['valueformat']);
                    $d->set('contextid', $context->id);
                    $d->save();
                }
                return;
            }
        }
    }

    /**
     * Returns the context for the data associated with the given instanceid.
     *
     * @param int $instanceid id of the record to get the context for
     * @return \context the context for the given record
     */
    public function get_instance_context(int $instanceid = 0): \context {
        if ($instanceid > 0) {
            return \context_module::instance($instanceid);
        } else {
            return \context_system::instance();
        }
    }

    /**
     * Returns the parent context for the course
     *
     * @return \context
     * @throws \dml_exception
     */
    protected function get_parent_context(): \context {
        global $PAGE;
        if ($this->parentcontext) {
            return $this->parentcontext;
        } else if ($PAGE->context && $PAGE->context instanceof \context_course) {
            return $PAGE->context;
        }
        return \context_system::instance();
    }

    /**
     * URL for configuration of the fields on this handler.
     *
     * @return \moodle_url The URL to configure custom fields for this component
     */
    public function get_configuration_url(): \moodle_url {
        return new \moodle_url('/local/resourcelibrary/activityfields.php');
    }

    /**
     * Set up page customfield/edit.php
     *
     * @param field_controller $field
     * @return string page heading
     */
    public function setup_edit_page(field_controller $field): string {
        return $this->setup_edit_page_with_external($field, 'resourcelibrary_coursemodule_customfield');
    }
}
