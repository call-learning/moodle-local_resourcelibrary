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
use local_resourcelibrary\item_type;
use local_resourcelibrary\item_visibility;
use local_resourcelibrary\tests\local_resourcelibrary_testcase;

/**
 * Tests for item_visibility_test static functions
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 * @coversDefaultClass \local_resourcelibrary\external\set_item_visibility
 * @covers \local_resourcelibrary\external\set_item_visibility::execute
 */
final class set_item_visibility_test extends local_resourcelibrary_testcase {
    /**
     * Test that we can obtain a single row result for a set of fields for a course and course module
     * get_filtered_courses($ids = array(), $filters = array(), $limit = 0, $offset = 0, $sorting = null) {
     *
     */
    public function test_set_item_visibilty(): void {
        $this->resetAfterTest(true);
        $data = [
            ['type' => 'cat', 'name' => 'cat1'],
            ['type' => 'cat', 'name' => 'cat2'],
            ['type' => 'cat', 'name' => 'cat3', 'parent' => 'cat1'],
            ['type' => 'course', 'name' => 'Course 1', 'category' => 'cat1'],
            ['type' => 'course', 'name' => 'Course 2', 'category' => 'cat2'],
            ['type' => 'course', 'name' => 'Course 3', 'category' => 'cat3'],
        ];
        ['categories' => $categories, 'courses' => $courses] = $this->create_structure($data);

        // Check that all courses are visible initially.
        $filteredcourses = get_filtered_courses::execute($categories['cat1']->id);
        $this->assertCount(2, $filteredcourses);
        // Hide category 1.
        $this->set_items_visibility([
            [
                'id' => 0,
                'itemid' => $categories['cat1']->id,
                'itemtype' => item_type::CATEGORY->value,
                'visibility' => item_visibility::HIDDEN->value,
            ],
        ]);
        // Check that courses in category 1 and its subcategory are hidden.
        $filteredcourses = get_filtered_courses::execute($categories['cat1']->id);
        $this->assertCount(0, $filteredcourses);
        // Now hide category 2 too.
        $this->set_items_visibility([
            [
                'id' => 0,
                'itemid' => $categories['cat2']->id,
                'itemtype' => item_type::CATEGORY->value,
                'visibility' => item_visibility::HIDDEN->value,

            ],
        ]);
        // Check that no course is visible now.
        $filteredcourses = get_filtered_courses::execute();
        $this->assertEmpty($filteredcourses);
        // Now unhide category 1.
        $this->set_items_visibility([
            [
                'id' => 0,
                'itemid' => $categories['cat1']->id,
                'itemtype' => item_type::CATEGORY->value,
                'visibility' => item_visibility::VISIBLE->value,
            ],
        ]);
        // Check that courses in category 1 and its subcategory are visible again.
        $filteredcourses = get_filtered_courses::execute();
        $this->assertCount(2, $filteredcourses);
        $this->assertEquals($courses['Course 1']->id, $filteredcourses[0]['id']);
        $this->assertEquals($courses['Course 3']->id, $filteredcourses[1]['id']);

        // Finally unhide category 2.
        $this->set_items_visibility([
            [
                'id' => 0,
                'itemid' => $categories['cat2']->id,
                'itemtype' => item_type::CATEGORY->value,
                'visibility' => item_visibility::VISIBLE->value,
            ],
        ]);
        // Check that all courses are visible again.
        $filteredcourses = get_filtered_courses::execute();
        $this->assertCount(3, $filteredcourses);
        $this->assertEquals($courses['Course 1']->id, $filteredcourses[0]['id']);
        $this->assertEquals($courses['Course 2']->id, $filteredcourses[1]['id']);
        $this->assertEquals($courses['Course 3']->id, $filteredcourses[2]['id']);

        // Hide course 2 only.
        $this->set_items_visibility([
            [
                'id' => 0,
                'itemid' => $courses['Course 2']->id,
                'itemtype' => item_type::COURSE->value,
                'visibility' => item_visibility::HIDDEN->value,
            ],
        ]);
        // Check that course 2 is hidden now.
        $filteredcourses = get_filtered_courses::execute();
        $this->assertCount(2, $filteredcourses);
        $this->assertEquals($courses['Course 1']->id, $filteredcourses[0]['id']);
        $this->assertEquals($courses['Course 3']->id, $filteredcourses[1]['id']);
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function set_items_visibility(...$params) {
        $getfilteredcourses = set_item_visibility::execute(...$params);

        return external_api::clean_returnvalue(set_item_visibility::execute_returns(), $getfilteredcourses);
    }

    /**
     * Create a structure of categories and courses based on the provided data.
     *
     * @param array $data
     * @return array
     */
    private function create_structure(array $data) {
        $dg = $this->getDataGenerator();
        $categories = [];
        $courses = [];
        foreach ($data as $item) {
            if ($item['type'] === 'cat') {
                $parentid = 0;
                if (array_key_exists('parent', $item)) {
                    $parentid = $categories[$item['parent']]->id;
                }
                $categories[$item['name']] = $dg->create_category(['name' => $item['name'], 'parent' => $parentid]);
            } else if ($item['type'] === 'course') {
                $courses[$item['name']] = $dg->create_course(
                    [
                        'name' => $item['name'],
                        'category' => $categories[$item['category']]->id,
                    ]
                );
            }
        }
        return ['categories' => $categories, 'courses' => $courses];
    }
}
