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
 * Resource Library test utills
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_resourcelibrary\locallib\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Generate basic custom fields
 *
 * @param testing_data_generator $dg
 */
function generate_category_and_fields($dg) {
    $generator = $dg->get_plugin_generator('local_resourcelibrary');
    foreach (array('core_course' => 'course', 'local_resourcelibrary' => 'coursemodule') as $component => $area) {
        $catid = $generator->create_category(['component' => $component, 'area' => $area])->get('id');
        $generator->create_field(['name' => 'Field 1', 'categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1',
            'area' => $area, 'component' => $component]);
        $generator->create_field(['name' => 'Field 2', 'categoryid' => $catid, 'type' => 'checkbox', 'shortname' => 'f2',
            'area' => $area, 'component' => $component]);
        $generator->create_field(['name' => 'Field 3', 'categoryid' => $catid, 'type' => 'date', 'shortname' => 'f3',
            'configdata' => ['startyear' => 2000, 'endyear' => 3000, 'includetime' => 1], 'area' => $area,
            'component' => $component]);
        if (utils::is_multiselect_installed()) {
            $generator->create_field(['name' => 'Field 4', 'categoryid' => $catid, 'type' => 'multiselect', 'shortname' => 'f4',
                'configdata' => ['options' => "a\nb\nc"], 'area' => $area, 'component' => $component]);
        }
        $generator->create_field(['name' => 'Field 5', 'categoryid' => $catid, 'type' => 'select', 'shortname' => 'f5',
            'configdata' => ['options' => "a\nb\nc"], 'area' => $area, 'component' => $component]);
        $generator->create_field(['name' => 'Field 6', 'categoryid' => $catid, 'type' => 'textarea', 'shortname' => 'f6',
            'area' => $area, 'component' => $component]);
    }
}

/**
 * Standard setup for resourcelibrary tests
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class local_resourcelibrary_testcase extends advanced_testcase {
    /**
     * @var int $now
     */
    protected $now = null;

    /**
     * Set up
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $dg = self::getDataGenerator();
        generate_category_and_fields($dg);

    }

    /**
     * Setup an array of simple custom field definition
     *
     * Take into account that multiselect custom field might not be installed
     *
     * @return array
     */
    protected function get_simple_cf_data() {
        if (!$this->now) {
            $this->now = time();
        }
        $simpledata = ['customfield_f1' => 'some text',
            'customfield_f2' => 1,
            'customfield_f3' => $this->now,
            'customfield_f5' => 2,
            'customfield_f6_editor' => ['text' => 'test', 'format' => FORMAT_HTML]];
        if (utils::is_multiselect_installed()) {
            $simpledata['customfield_f4'] = [1, 2];
        }
        return $simpledata;
    }

    /**
     * Simple assertion for the custom field data
     *
     * @param stdClass $data
     */
    protected function assert_check_simple_cf_data($data) {
        $this->assertEquals('some text', $data->customfield_f1);
        $this->assertEquals(1, $data->customfield_f2);
        $this->assertEquals($this->now, $data->customfield_f3);
        if (utils::is_multiselect_installed()) {
            $this->assertEquals('1,2', $data->customfield_f4);
        }
        $this->assertEquals(2, $data->customfield_f5);
        $this->assertEquals('test', $data->customfield_f6);
    }

    /**
     * Simple assertion for the custom field data
     * @param stdClass $data
     * @throws coding_exception
     */
    protected function assert_check_simple_cf_data_exported($data) {
        $this->assertEquals('some text', $data->f1);
        $this->assertEquals('Yes', $data->f2);
        $this->assertEquals(userdate($this->now, get_string('strftimedaydatetime')), $data->f3);
        if (utils::is_multiselect_installed()) {
            $this->assertEquals('b, c', $data->f4);
        }
        $this->assertEquals('b', $data->f5);
        $this->assertEquals('test', $data->f6);
    }
}