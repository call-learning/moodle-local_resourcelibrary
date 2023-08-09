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
namespace local_resourcelibrary;
use local_resourcelibrary\locallib\utils;
use local_resourcelibrary_external;
use local_resourcelibrary_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/local/resourcelibrary/tests/lib.php');

/**
 * Tests for externallib static functions
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_test extends local_resourcelibrary_testcase {


    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null) {
     * @covers \local_resourcelibrary\local_resourcelibrary_external::get_filtered_courses
     */
    public function test_get_filtered_courses_simple() {
        global $CFG;
        require_once($CFG->dirroot . '/local/resourcelibrary/externallib.php');

        $dg = $this->getDataGenerator();

        $data = ['shortname' => 'SN', 'fullname' => 'FN',
            'summary' => 'DESC', 'summaryformat' => FORMAT_MOODLE] +
            $this->get_simple_cf_data();
        $dg->create_course($data);

        $courses = local_resourcelibrary_external::get_filtered_courses();

        $this->assertCount(1, $courses);
    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null)
     * @covers \local_resourcelibrary\local_resourcelibrary_external::get_filtered_courses
     */
    public function test_get_filtered_modules_simple() {
        global $CFG;
        require_once($CFG->dirroot . '/local/resourcelibrary/externallib.php');

        $dg = $this->getDataGenerator();

        $moduledata = $this->get_simple_cf_data();
        if (utils::is_multiselect_installed()) {
            $moduledata['customfield_f4'] = [1, 2];
        }
        $c1 = $dg->create_course();
        foreach (array('page', 'label', 'page', 'label') as $modtype) {
            $dg->create_module($modtype, array_merge(
                ['course' => $c1->id],
                $moduledata
            ));
        }
        $modules = local_resourcelibrary_external::get_filtered_course_content($c1->id);

        $this->assertCount(4, $modules);
    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null) {
     * @covers \local_resourcelibrary\local_resourcelibrary_external::get_filtered_courses
     */
    public function test_get_filtered_modules_single_criteria() {
        global $CFG;
        require_once($CFG->dirroot . '/local/resourcelibrary/externallib.php');

        $dg = $this->getDataGenerator();

        $now = time();

        $c1 = $dg->create_course();
        foreach (array('page', 'label', 'page', 'label') as $index => $modtype) {
            $moduledata = [
                'customfield_f1' => 'some text' . $index,
                'customfield_f2' => $index % 2,
                'customfield_f3' => $now + ((0 - $index % 2) * 20000),
                'customfield_f5' => (1 + ($index % 2)),
                'customfield_f6_editor' => ['text' => 'test', 'format' => FORMAT_HTML]];
            if (utils::is_multiselect_installed()) {
                $moduledata['customfield_f4'] = [1, 2];
            }
            $dg->create_module($modtype, array_merge(
                ['course' => $c1->id],
                $moduledata
            ));
        }

        $modules = local_resourcelibrary_external::get_filtered_course_content($c1->id, array(array(
            'type' => 'select',
            'shortname' => 'f5',
            'operator' => 1,
            'value' => '2',
        )));

        $this->assertCount(2, $modules);
    }
}
