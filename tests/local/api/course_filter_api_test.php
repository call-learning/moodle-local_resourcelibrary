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

namespace local_resourcelibrary\local\api;

use local_resourcelibrary\tests\local_resourcelibrary_testcase;
use local_resourcelibrary\local\persistent\catalogue_page;

/**
 * Tests for course_filter_api class
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_resourcelibrary\local\api\course_filter_api
 */
final class course_filter_api_test extends local_resourcelibrary_testcase {
    /**
     * Test basic course filtering without any parameters
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

        $courses = course_filter_api::get_filtered_courses();

        $this->assertCount(1, $courses);
        $this->assertEquals('FN', $courses[0]['fullname']);
        $this->assertEquals('SN', $courses[0]['shortname']);
    }

    /**
     * Test filtering courses by category using legacy categoryid parameter
     */
    public function test_get_filtered_courses_by_category(): void {
        $dg = $this->getDataGenerator();

        // Create categories.
        $cat1 = $dg->create_category(['name' => 'Category 1']);
        $cat2 = $dg->create_category(['name' => 'Category 2']);

        // Create courses in different categories.
        $course1 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 1']);
        $course2 = $dg->create_course(['category' => $cat2->id, 'fullname' => 'Course 2']);
        $course3 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 3']);

        // Test filtering by single category using legacy categoryid parameter.
        $courses = course_filter_api::get_filtered_courses(
            0,
            [],
            0,
            0,
            [],
            $cat1->id
        );
        $this->assertCount(2, $courses);

        // Test filtering by different category.
        $courses = course_filter_api::get_filtered_courses(
            0,
            [],
            0,
            0,
            [],
            $cat2->id
        );
        $this->assertCount(1, $courses);
        $this->assertEquals('Course 2', $courses[0]['fullname']);
    }

    /**
     * Test filtering courses with catalogue page configuration
     */
    public function test_get_filtered_courses_with_pageid(): void {
        $dg = $this->getDataGenerator();

        // Create categories.
        $cat1 = $dg->create_category(['name' => 'Category 1']);
        $cat2 = $dg->create_category(['name' => 'Category 2']);

        // Create courses in different categories.
        $course1 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 1']);
        $course2 = $dg->create_course(['category' => $cat2->id, 'fullname' => 'Course 2']);
        $course3 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 3']);

        // Create a catalogue page with specific categories.
        $cataloguepage = new catalogue_page();
        $cataloguepage->set('name', 'Test Catalogue Page');
        $cataloguepage->set_categories_array([$cat1->id]);
        $cataloguepage->create();

        // Test filtering using pageid.
        $courses = course_filter_api::get_filtered_courses(
            $cataloguepage->get('id'),
            [],
            0,
            0,
            []
        );
        $this->assertCount(2, $courses); // Should only return courses from cat1.

        // Verify the correct courses are returned.
        $coursenames = array_column($courses, 'fullname');
        $this->assertContains('Course 1', $coursenames);
        $this->assertContains('Course 3', $coursenames);
        $this->assertNotContains('Course 2', $coursenames);

        // Test with pageid = 0 (show all courses).
        $courses = course_filter_api::get_filtered_courses(
            0,
            [],
            0,
            0,
            []
        );
        $this->assertCount(3, $courses); // Should return all courses.
    }

    /**
     * Test filtering with custom field filters
     */
    public function test_get_filtered_courses_with_custom_field_filters(): void {
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
            $coursedata = [
                    'shortname' => "SN $index",
                    'fullname' => "FN $index",
                    'summary' => "<p>DESC {$index} </p>",
                    'summaryformat' => FORMAT_MOODLE,
                ] + $this->get_simple_cf_data($fieldsdata[$index]);
            $courses[] = $dg->create_course($coursedata);
        }

        // Filter on f1 = 'Text 2'.
        $coursesfound = course_filter_api::get_filtered_courses(0, [
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

        // Filter on f5 = '2'.
        $coursesfound = course_filter_api::get_filtered_courses(0, [
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
     * Test pagination functionality
     */
    public function test_get_filtered_courses_pagination(): void {
        $dg = $this->getDataGenerator();

        // Create multiple courses.
        for ($i = 1; $i <= 5; $i++) {
            $dg->create_course([
                'shortname' => "SN$i",
                'fullname' => "Course $i",
                'summary' => "Description $i",
            ]);
        }

        // Test limit.
        $courses = course_filter_api::get_filtered_courses(0, [], 3, 0, []);
        $this->assertCount(3, $courses);

        // Test offset.
        $courses = course_filter_api::get_filtered_courses(0, [], 2, 2, []);
        $this->assertCount(2, $courses);

        // Test limit and offset combined.
        $courses = course_filter_api::get_filtered_courses(0, [], 2, 1, []);
        $this->assertCount(2, $courses);
    }

    /**
     * Test sorting functionality
     */
    public function test_get_filtered_courses_sorting(): void {
        $dg = $this->getDataGenerator();

        // Create courses with different names.
        $course1 = $dg->create_course(['fullname' => 'Z Course']);
        $course2 = $dg->create_course(['fullname' => 'A Course']);
        $course3 = $dg->create_course(['fullname' => 'M Course']);

        // Test sorting by fullname ASC.
        $courses = course_filter_api::get_filtered_courses(0, [], 0, 0, [
            ['column' => 'fullname', 'order' => 'ASC'],
        ]);

        $this->assertCount(3, $courses);
        $this->assertEquals('A Course', $courses[0]['fullname']);
        $this->assertEquals('M Course', $courses[1]['fullname']);
        $this->assertEquals('Z Course', $courses[2]['fullname']);

        // Test sorting by fullname DESC.
        $courses = course_filter_api::get_filtered_courses(0, [], 0, 0, [
            ['column' => 'fullname', 'order' => 'DESC'],
        ]);

        $this->assertEquals('Z Course', $courses[0]['fullname']);
        $this->assertEquals('M Course', $courses[1]['fullname']);
        $this->assertEquals('A Course', $courses[2]['fullname']);
    }

    /**
     * Test get_hidden_course_ids functionality
     */
    public function test_get_hidden_course_ids(): void {
        $hiddenids = course_filter_api::get_hidden_course_ids();
        $this->assertIsArray($hiddenids);
        // By default, no courses should be hidden.
        $this->assertEmpty($hiddenids);
    }

    /**
     * Test that catalogue page with empty categories shows all courses
     */
    public function test_catalogue_page_empty_categories(): void {
        $dg = $this->getDataGenerator();

        // Create categories and courses.
        $cat1 = $dg->create_category(['name' => 'Category 1']);
        $course1 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 1']);
        $course2 = $dg->create_course(['category' => $cat1->id, 'fullname' => 'Course 2']);

        // Create a catalogue page with no categories configured.
        $cataloguepage = new catalogue_page();
        $cataloguepage->set('name', 'Empty Catalogue Page');
        $cataloguepage->set_categories_array([]);
        $cataloguepage->create();

        // Should show all courses when no categories are configured.
        $courses = course_filter_api::get_filtered_courses(
            $cataloguepage->get('id'),
            [],
            0,
            0,
            []
        );
        $this->assertCount(2, $courses);
    }

    /**
     * Test that course filtering includes subcategories
     */
    public function test_get_filtered_courses_includes_subcategories(): void {
        $dg = $this->getDataGenerator();

        // Create parent category.
        $parentcat = $dg->create_category(['name' => 'Parent Category']);

        // Create subcategory.
        $subcat = $dg->create_category([
            'name' => 'Sub Category',
            'parent' => $parentcat->id,
        ]);

        // Create courses in both categories.
        $course1 = $dg->create_course(['category' => $parentcat->id, 'fullname' => 'Parent Course']);
        $course2 = $dg->create_course(['category' => $subcat->id, 'fullname' => 'Sub Course']);

        // Create catalogue page with parent category.
        $cataloguepage = new catalogue_page();
        $cataloguepage->set('name', 'Parent Category Page');
        $cataloguepage->set_categories_array([$parentcat->id]);
        $cataloguepage->create();

        // Should return courses from both parent and subcategory.
        $courses = course_filter_api::get_filtered_courses(
            $cataloguepage->get('id'),
            [],
            0,
            0,
            []
        );

        $this->assertCount(2, $courses);
        $coursenames = array_column($courses, 'fullname');
        $this->assertContains('Parent Course', $coursenames);
        $this->assertContains('Sub Course', $coursenames);
    }

    /**
     * Test error handling for non-existent catalogue page
     */
    public function test_get_filtered_courses_invalid_pageid(): void {
        $dg = $this->getDataGenerator();

        // Create a course.
        $course1 = $dg->create_course(['fullname' => 'Test Course']);

        // Test with non-existent pageid - should show all courses.
        $courses = course_filter_api::get_filtered_courses(
            999999,
            [],
            0,
            0,
            []
        );

        $this->assertCount(1, $courses);
        $this->assertEquals('Test Course', $courses[0]['fullname']);
    }
}
