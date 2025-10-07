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

use local_resourcelibrary\tests\local_resourcelibrary_testcase;
use local_resourcelibrary\local\api\course_filter_api;

/**
 * Legacy tests for get_filtered_course functionality
 *
 * Note: This test file is deprecated and will be removed in a future version.
 * Use \local_resourcelibrary\local\api\course_filter_api_test instead.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_resourcelibrary\local\api\course_filter_api
 * @deprecated Use course_filter_api_test instead
 * @runTestsInSeparateProcesses
 */
final class get_filtered_course_test extends local_resourcelibrary_testcase {
    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
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
     * Helper - directly use course_filter_api instead of external service
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_filtered_courses(...$params) {
        // Use the new API directly instead of the external service
        return course_filter_api::get_filtered_courses(...$params);
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

    /**
     * Test the new API directly for filtering by categories
     */
    public function test_course_filter_api_categories(): void {
        $dg = $this->getDataGenerator();

        // Create categories
        $cat1 = $dg->create_category(['name' => 'Category 1']);
        $cat2 = $dg->create_category(['name' => 'Category 2']);

        // Create courses in different categories
        $course1 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 1']);
        $course2 = $dg->create_course(['category' => $cat2->id, 'fullname' => 'Course 2']);
        $course3 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 3']);

        // Test filtering by single category using legacy categoryid parameter
        $courses = course_filter_api::get_filtered_courses(
            0, [], 0, 0, [], $cat1->id
        );
        $this->assertCount(2, $courses);

        // Test filtering by multiple categories using legacy categoryid parameter
        $courses = course_filter_api::get_filtered_courses(
            0, [], 0, 0, [], $cat2->id
        );
        $this->assertCount(1, $courses);
    }

    /**
     * Test the new API for getting hidden course IDs
     */
    public function test_course_filter_api_hidden_courses(): void {
        $hiddenids = course_filter_api::get_hidden_course_ids();
        $this->assertIsArray($hiddenids);
    }

    /**
     * Test the new API with catalogue page filtering
     */
    public function test_course_filter_api_with_pageid(): void {
        $dg = $this->getDataGenerator();

        // Create categories
        $cat1 = $dg->create_category(['name' => 'Category 1']);
        $cat2 = $dg->create_category(['name' => 'Category 2']);

        // Create courses in different categories
        $course1 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 1']);
        $course2 = $dg->create_course(['category' => $cat2->id, 'fullname' => 'Course 2']);
        $course3 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 3']);

        // Create a catalogue page with specific categories
        $cataloguepage = new \local_resourcelibrary\local\persistent\catalogue_page();
        $cataloguepage->set('name', 'Test Catalogue Page');
        $cataloguepage->set_categories_array([$cat1->id]);
        $cataloguepage->create();

        // Test filtering using pageid
        $courses = course_filter_api::get_filtered_courses(
            $cataloguepage->get('id'), [], 0, 0, []
        );
        $this->assertCount(2, $courses); // Should only return courses from cat1

        // Test with pageid = 0 (show all courses)
        $courses = course_filter_api::get_filtered_courses(
            0, [], 0, 0, []
        );
        $this->assertCount(3, $courses); // Should return all courses
    }
}
