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
 * Updates
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for local_resourcelibrary
 *
 * @param string $oldversion
 * @return bool
 * @throws coding_exception
 * @throws downgrade_exception
 * @throws upgrade_exception|dml_exception
 */
function xmldb_local_resourcelibrary_upgrade($oldversion) {

    // Always keep this upgrade step with version being the minimum
    // allowed version to upgrade from (v3.2.0 right now).
    if ($oldversion < 2020042002) {
        upgrade_plugin_savepoint(true, 2020042002, 'local', 'resourcelibrary');
    }

    if ($oldversion < 2020042003) {
        \local_resourcelibrary\locallib\setup::setup_resourcelibrary_custom_fields();
        upgrade_plugin_savepoint(true, 2020042003, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042005) {
        \local_resourcelibrary\locallib\setup::setup_resourcelibrary_custom_fields();
        upgrade_plugin_savepoint(true, 2020042005, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042006) {
        upgrade_plugin_savepoint(true, 2020042006, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042007) {
        upgrade_plugin_savepoint(true, 2020042007, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042008) {
        upgrade_plugin_savepoint(true, 2020042008, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042009) {
        upgrade_plugin_savepoint(true, 2020042009, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042010) {
        upgrade_plugin_savepoint(true, 2020042010, 'local', 'resourcelibrary');
    }
    if ($oldversion < 2020042013) {
        global $DB;
        // Change all specialised course custom field (old course_handler class) into usual course custom fields.
        $coursefields =
            $DB->get_records('customfield_category', array('area' => 'course', 'component' => 'local_resourcelibrary'));
        foreach ($coursefields as $cf) {
            $cf->component = 'core_course';
            $DB->update_record('customfield_category', $cf);
        }
        upgrade_plugin_savepoint(true, 2020042013, 'local', 'resourcelibrary');
    }
    return true;
}
