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

namespace local_resourcelibrary\external;

use core_external\external_api;
use local_resourcelibrary\tests\local_resourcelibrary_testcase;

/**
 * Tests for get_filtered_course_test static functions
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_resourcelibrary\local_resourcelibrary_external::get_filtered_courses
 * @runTestsInSeparateProcesses
 */
final class get_filtered_course_test extends local_resourcelibrary_testcase {
    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null)
     */
    public function test_get_filtered_courses_simple(): void {
        $dg = $this->getDataGenerator();

        $data = [
                'shortname' => 'SN',
                'fullname' => 'FN',
                'summary' => 'DESC',
                'summaryformat' => FORMAT_MOODLE,
            ] +
            $this->get_simple_cf_data();
        $dg->create_course($data);

        $courses = $this->get_filtered_courses();

        $this->assertCount(1, $courses);
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_filtered_courses(...$params) {
        $getfilteredcourses = get_filtered_courses::execute(...$params);

        return external_api::clean_returnvalue(get_filtered_courses::execute_returns(), $getfilteredcourses);
    }

    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null) {
     */
    public function test_get_filtered_courses_single_criteria(): void {
        $dg = $this->getDataGenerator();

        $fieldsdata = [
            [
                'f1' => 'Text 1',
                'f5' => 1,
            ],
            [
                'f1' => 'Text 2',
                'f5' => 2,
            ],
            [
                'f1' => 'Text 2',
                'f5' => 1,
            ],
        ];

        $courses = [];
        foreach ($fieldsdata as $index => $fielddata) {
            $coursdata = [
                    'shortname' => "SN $index",
                    'fullname' => "FN $index",
                    'summary' => "<p>DESC {$index} </p>",
                    'summaryformat' => FORMAT_MOODLE,
                ] + $this->get_simple_cf_data($fieldsdata[$index]);
            $courses[] = $dg->create_course($coursdata);
        }

        // Filter on f1 = 'Text 2'.
        $coursesfound = $this->get_filtered_courses(0, [
            [
                'type' => 'text',
                'shortname' => 'f1',
                'operator' => 1,
                'value' => 'Text 2',
            ],
        ]);
        $this->assertCount(2, $coursesfound);
        $this->assertEquals($courses[1]->id, $coursesfound[0]['id']);
        $this->assertEquals($courses[2]->id, $coursesfound[1]['id']);
        // Filter on f5 = 'Text 2'.
        $coursesfound = $this->get_filtered_courses(0, [
            [
                'type' => 'text',
                'shortname' => 'f5',
                'operator' => 1,
                'value' => '2',
            ],
        ]);
        $this->assertCount(1, $coursesfound);
        $this->assertEquals($courses[1]->id, $coursesfound[0]['id']);
    }
}
