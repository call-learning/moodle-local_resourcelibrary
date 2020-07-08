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
 * Backup for information in the Resource Library
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class extending standard backup_plugin
 *
 * This class implements some helper methods related with the Resource Library plugin
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_resourcelibrary_plugin extends backup_plugin {

    /**
     * Define the plugin structure for backup.
     *
     * @throws base_element_struct_exception
     */
    public function define_module_plugin_structure() {
        $rlfields = new backup_optigroup_element('resourcelibraryfields');
        $resourcelibraryfield = new backup_nested_element('resourcelibraryfield', array('id'), array(
            'shortname', 'type', 'value', 'valueformat'
        ));
        $rlfields->add_child($resourcelibraryfield);

        $this->optigroup->add_child($rlfields);
        $handler = local_resourcelibrary\customfield\coursemodule_handler::create();
        $fieldsforbackup = $handler->get_instance_data_for_backup($this->task->get_moduleid());
        $resourcelibraryfield->set_source_array($fieldsforbackup);
    }
}