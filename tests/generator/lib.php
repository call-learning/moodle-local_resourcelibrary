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
 * Resource Library data generator.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \core_customfield\category_controller;
use \core_customfield\field_controller;
global $CFG;

require_once($CFG->dirroot.'/customfield/tests/generator/lib.php');

/**
 * Resource Library data generator.
 *
 * Pretty much the same as custom field generator
 * @package    local_resourcelibrary
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourcelibrary_generator extends core_customfield_generator {
    /** @var int Number of created categories and field for each entity. */
    protected $counts = array(
        'course' => array('categorycount' => 0, 'fieldcount' => 0),
        'coursemodule' => array('categorycount' => 0, 'fieldcount' => 0),
    );

    /**
     * Create a new category.
     *
     * @param array|stdClass $record
     * @return category_controller
     * @throws moodle_exception
     */
    public function create_category($record = null, $type = 'course') {
        $this->counts[$type]['categorycount']++;
        $i = $this->counts[$type]['categorycount'];
        $record = (object) $record;
        $record->area = $type;

        if (!isset($record->name)) {
            $record->name = "Category $i";
        }
        if (!isset($record->component)) {
            $record->component = 'local_resourcelibrary';
        }

        if (!isset($record->itemid)) {
            $record->itemid = 0;
        }

        $handler = \core_customfield\handler::get_handler($record->component, $record->area, $record->itemid);
        $categoryid = $handler->create_category($record->name);
        return $handler->get_categories_with_fields()[$categoryid];
    }

    /**
     * Create a new field.
     *
     * @param array|stdClass $record
     * @return field_controller
     */
    public function create_field($record, $type='course'): field_controller {
        $this->counts[$type]['fieldcount']++;
        $i = $this->counts[$type]['fieldcount'];
        $record = (object) $record;

        if (empty($record->categoryid)) {
            throw new coding_exception('The categoryid value is required.');
        }
        $category = category_controller::create($record->categoryid);
        $handler = $category->get_handler();

        if (!isset($record->name)) {
            $record->name = "Field $i";
        }
        if (!isset($record->shortname)) {
            $record->shortname = "fld$i";
        }
        if (!isset($record->description)) {
            $record->description = "Field $i description";
        }
        if (!isset($record->descriptionformat)) {
            $record->descriptionformat = FORMAT_HTML;
        }
        if (!isset($record->type)) {
            $record->type = 'text';
        }
        if (!isset($record->sortorder)) {
            $record->sortorder = 0;
        }

        if (empty($record->configdata)) {
            $configdata = [];
        } else if (is_array($record->configdata)) {
            $configdata = $record->configdata;
        } else {
            $configdata = @json_decode($record->configdata, true);
            $configdata = $configdata ?? [];
        }
        $configdata += [
            'required' => 0,
            'uniquevalues' => 0,
            'locked' => 0,
            'visibility' => 2,
            'defaultvalue' => '',
            'displaysize' => 0,
            'maxlength' => 0,
            'ispassword' => 0,
            'link' => '',
            'linktarget' => '',
            'checkbydefault' => 0,
            'startyear' => 2000,
            'endyear' => 3000,
            'includetime' => 1,
        ];
        $record->configdata = json_encode($configdata);

        $field = field_controller::create(0, (object) ['type' => $record->type], $category);
        $handler->save_field_configuration($field, $record);
        return $handler->get_categories_with_fields()[$field->get('categoryid')]->get_fields()[$field->get('id')];
    }
}
