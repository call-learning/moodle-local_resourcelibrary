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

use local_resourcelibrary\customfield\course_handler;
use local_resourcelibrary\customfield\coursemodule_handler;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Tests for externallib static functions
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourcelibrary_api_testcase extends advanced_testcase {

    /**
     * Set up
     */
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $dg = self::getDataGenerator();
        $generator = $dg->get_plugin_generator('local_resourcelibrary');
        foreach (array('course', 'coursemodule') as $type) {
            $catid = $generator->create_category([], $type)->get('id');
            $generator->create_field(['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1'], $type);
            $generator->create_field(['categoryid' => $catid, 'type' => 'checkbox', 'shortname' => 'f2'], $type);
            $generator->create_field(['categoryid' => $catid, 'type' => 'date', 'shortname' => 'f3',
                'configdata' => ['startyear' => 2000, 'endyear' => 3000, 'includetime' => 1]], $type);
            $generator->create_field(['categoryid' => $catid, 'type' => 'select', 'shortname' => 'f4',
                'configdata' => ['options' => "a\nb\nc"]], $type);
            $dg->create_custom_field(['categoryid' => $catid, 'type' => 'textarea', 'shortname' => 'f5']);
        }
    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null) {
     */
    public function test_get_filtered_courses_simple() {
        global $CFG;
        require_once($CFG->dirroot . '/local/resourcelibrary/externallib.php');

        $dg = $this->getDataGenerator();

        $now = time();
        $data = ['shortname' => 'SN', 'fullname' => 'FN',
            'summary' => 'DESC', 'summaryformat' => FORMAT_MOODLE,
            'customfield_f1' => 'some text',
            'customfield_f2' => 1,
            'customfield_f3' => $now,
            'customfield_f4' => 2,
            'customfield_f5_editor' => ['text' => 'test', 'format' => FORMAT_HTML]];
        $c1 = $dg->create_course($data);

        $data['id'] = $c1->id;
        \local_resourcelibrary\locallib\utils::course_update_fields((object) $data);

        $courses = local_resourcelibrary_external::get_filtered_courses();

        $this->assertCount(1, $courses);
        $coursedata = $courses[0];
        $rlfields = $coursedata['resourcelibraryfields'];
        $this->assertCount(5, $rlfields);
    }

}