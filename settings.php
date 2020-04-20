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
 * This file defines settingpages and externalpages under the "courses" category
 *
 * @package    local_imtcatalog
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ($hassiteconfig or has_any_capability($capabilities, $systemcontext)) {
    $settings = new admin_settingpage('local_imtcatalog', get_string('pluginname', 'local_imtcatalog'));
    $settings->add(
        new admin_externalpage('catalog_coursemodule_customfield',
            new lang_string('catalog_coursemodule_customfield', 'local_imtcatalog'),
            $CFG->wwwroot . '/local/imtcatalog/activityfields.php',
            array('local/imtcatalog:manage')
        )
    );
    $settings->add(
        new admin_externalpage('catalog_coursemodule_customfield',
            new lang_string('catalog_coursemodule_customfield', 'local_imtcatalog'),
            $CFG->wwwroot . '/local/imtcatalog/activityfields.php',
            array('local/imtcatalog:manage')
        )
    );
    if (!empty($CFG->enableimtcatalog) && $CFG->enableimtcatalog) {
        $ADMIN->add('courses', $settings); // Add it to the course menu.
    }
    // Create a global Advanced Feature Toggle.
    $enableoption = new admin_setting_configcheckbox('enableimtcatalog',
        new lang_string('enableimtcatalog', 'local_imtcatalog'),
        new lang_string('enableimtcatalog', 'local_imtcatalog'),
        1);
    $enableoption->set_updatedcallback('local_imtcatalog_enable_disable_plugin_callback');

    $optionalsubsystems = $ADMIN->locate('optionalsubsystems');
    $optionalsubsystems->add($enableoption);
}
