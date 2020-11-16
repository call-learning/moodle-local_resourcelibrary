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
 * Tests for resourcelibraryfields in courses and modules
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_course\customfield\course_handler;
use local_resourcelibrary\locallib\utils;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/local/resourcelibrary/tests/lib.php');

/**
 * Tests for customfields in courses
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourcelibrary_filter_testcase extends local_resourcelibrary_testcase {

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     */
    public function test_flat_sql_course() {
        global $DB;
        $dg = $this->getDataGenerator();

        $data = ['shortname' => 'SN', 'fullname' => 'FN',
            'summary' => 'DESC', 'summaryformat' => FORMAT_MOODLE] + $this->get_simple_cf_data();
        $c1 = $dg->create_course($data);

        $activitydata = array('course' => $c1->id) + $this->get_simple_cf_data();

        // TODO: It would have been nice here to prefix the form and the values by 'resourcelibrary'.
        // But the datacontroller for each class (checkbox) will answer customfield_xx for the element name which
        // makes it impossible to prefix the Resource Library field by anything else than 'customfield_'.
        $dg->create_module('label', (object) $activitydata);
        $sqlcourse = \local_resourcelibrary\locallib\customfield_utils::get_sql_for_entity_customfields('course');
        $sqlcm = \local_resourcelibrary\locallib\customfield_utils::get_sql_for_entity_customfields('coursemodule');
        $courserow = $DB->get_records_sql($sqlcourse . ' WHERE e.id =' . $c1->id);
        $activityrow = $DB->get_records_sql($sqlcm);
        $this->assertCount(1, $courserow);
        $this->assertCount(1, $activityrow);
        foreach (array(reset($courserow), reset($activityrow)) as $data) {
            $this->assert_check_simple_cf_data($data);
        }
    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     */
    public function test_utils_get_hiddenfields_course() {
        $this->resetAfterTest();
        $dg = $this->getDataGenerator();

        $data = ['shortname' => 'SN', 'fullname' => 'FN',
            'summary' => 'DESC', 'summaryformat' => FORMAT_MOODLE ] + $this->get_simple_cf_data();
        $c1 = $dg->create_course($data);
        $handler = course_handler::create($c1->id);
        // Set the first field as hidden.
        set_config(utils::get_hidden_filter_config_name($handler), 'f1', 'local_resourcelibrary');

        $this->assertTrue(utils::is_field_hidden_filters($handler, 'f1'));
    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     */
    public function test_utils_set_get_hiddenfields_course() {
        $this->resetAfterTest();
        $dg = $this->getDataGenerator();

        $data = ['shortname' => 'SN', 'fullname' => 'FN',
            'summary' => 'DESC', 'summaryformat' => FORMAT_MOODLE] + $this->get_simple_cf_data();
        $c1 = $dg->create_course($data);
        $handler = course_handler::create($c1->id);

        // Test the two ways to call this method (int and array of int).
        utils::hide_fields_filter($handler, ['f1', 'f2']);
        utils::hide_fields_filter($handler, 'f3');

        $this->assertTrue(utils::is_field_hidden_filters($handler, 'f1'));
        $this->assertTrue(utils::is_field_hidden_filters($handler, 'f2'));
        $this->assertTrue(utils::is_field_hidden_filters($handler, 'f3'));

        $this->assertFalse(utils::is_field_hidden_filters($handler, 'f5'));

    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     */
    public function test_utils_show_hiddenfields_course() {
        $this->resetAfterTest();
        $dg = $this->getDataGenerator();

        $data = ['shortname' => 'SN', 'fullname' => 'FN',
            'summary' => 'DESC', 'summaryformat' => FORMAT_MOODLE] + $this->get_simple_cf_data();
        $c1 = $dg->create_course($data);
        $handler = course_handler::create($c1->id);
        $fields = $handler->get_fields();
        // Set the first field as hidden.
        utils::hide_fields_filter($handler, [
            'f1',
            'f2',
            'f3',
            'f5'
        ]);

        // Test the two ways to call this method (int and array of int).
        utils::show_fields_filter($handler, 'f1');
        utils::show_fields_filter($handler, ['f3', 'f5']);
        $this->assertFalse(utils::is_field_hidden_filters($handler, 'f1'));
        $this->assertTrue(utils::is_field_hidden_filters($handler, 'f2'));
        $this->assertFalse(utils::is_field_hidden_filters($handler, 'f3'));
        $this->assertFalse(utils::is_field_hidden_filters($handler, 'f5'));

    }
}
