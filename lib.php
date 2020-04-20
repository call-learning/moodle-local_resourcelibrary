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
 * Add form hooks for course and modules
 *
 * @package    local_imtcatalog
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


/**
 * Inject the competencies elements into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function local_imtcatalog_coursemodule_standard_elements($formwrapper, $mform) {
    global $CFG, $COURSE;

    if (empty($CFG->enableimtcatalog)) {
        return;
    } else if (!has_capability('local/imtcatalog:manage', $formwrapper->get_context())) {
        return;
    }

    $mform->addElement('header', 'catalogmetadata', get_string('catalogmetadata', 'local_imtcatalog'));
    $handler = \local_imtcatalog\customfield\coursemodule_handler::create();
    $handler->instance_form_definition($mform);
}


function local_imtcatalog_enable_disable_plugin_callback() {

}