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
 * Unit tests for item_visibility_api.
 *
 * @package   local_resourcelibrary
 * @category  test
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\tests\local\api;

use local_resourcelibrary\local\api\item_visibility_api;
use local_resourcelibrary\item_type;
use local_resourcelibrary\item_visibility;
use local_resourcelibrary\tests\local_resourcelibrary_testcase;

/**
 * Unit tests for item_visibility_api.
 *
 * @package   local_resourcelibrary
 * @category  test
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \local_resourcelibrary\local\api\item_visibility_api
 */
class item_visibility_api_test extends local_resourcelibrary_testcase {

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test setting visibility for a single course item.
     *
     * @covers ::set_items_visibility
     */
    public function test_set_single_course_visibility(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        $items = [
            [
                'id' => 0,
                'itemid' => $course->id,
                'itemtype' => item_type::COURSE->value,
                'visibility' => item_visibility::HIDDEN->value,
            ]
        ];

        $result = item_visibility_api::set_items_visibility($items);

        $this->assertEmpty($result['warnings']);
        $this->assertCount(1, $result['returneditems']);

        $item = $result['returneditems'][0];
        $this->assertEquals($course->id, $item->itemid);
        $this->assertEquals(item_type::COURSE->value, $item->itemtype);
        $this->assertEquals(item_visibility::HIDDEN->value, $item->visibility);

        // Verify database record
        $record = $DB->get_record('local_resourcelibrary', [
            'itemid' => $course->id,
            'itemtype' => item_type::COURSE->value
        ]);
        $this->assertNotFalse($record);
        $this->assertEquals(item_visibility::HIDDEN->value, $record->visibility);
    }

    /**
     * Test setting visibility for a category and its children.
     *
     * @covers ::set_items_visibility
     * @covers ::get_category_tree
     */
    public function test_set_category_visibility_with_children(): void {
        $data = [
            ['type' => 'cat', 'name' => 'cat1'],
            ['type' => 'cat', 'name' => 'cat2', 'parent' => 'cat1'],
            ['type' => 'course', 'name' => 'Course 1', 'category' => 'cat1'],
            ['type' => 'course', 'name' => 'Course 2', 'category' => 'cat2'],
        ];
        ['categories' => $categories, 'courses' => $courses] = $this->create_structure($data);

        $items = [
            [
                'id' => 0,
                'itemid' => $categories['cat1']->id,
                'itemtype' => item_type::CATEGORY->value,
                'visibility' => item_visibility::HIDDEN->value,
            ]
        ];

        $result = item_visibility_api::set_items_visibility($items);

        $this->assertEmpty($result['warnings']);
        // Should return 4 items: 2 courses + 2 categories
        $this->assertCount(4, $result['returneditems']);

        // Verify all items are hidden
        foreach ($result['returneditems'] as $item) {
            $this->assertEquals(item_visibility::HIDDEN->value, $item->visibility);
        }
    }

    /**
     * Test getting visibility status for a single item.
     *
     * @covers ::get_item_visibility
     */
    public function test_get_item_visibility(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        // Initially, no record should exist
        $visibility = item_visibility_api::get_item_visibility($course->id, item_type::COURSE->value);
        $this->assertNull($visibility);

        // Create a visibility record
        $record = new \stdClass();
        $record->itemid = $course->id;
        $record->itemtype = item_type::COURSE->value;
        $record->visibility = item_visibility::HIDDEN->value;
        $record->usermodified = 2;
        $record->timecreated = time();
        $record->timemodified = time();
        $DB->insert_record('local_resourcelibrary', $record);

        // Now it should return the record
        $visibility = item_visibility_api::get_item_visibility($course->id, item_type::COURSE->value);
        $this->assertNotNull($visibility);
        $this->assertEquals($course->id, $visibility->itemid);
        $this->assertEquals(item_type::COURSE->value, $visibility->itemtype);
        $this->assertEquals(item_visibility::HIDDEN->value, $visibility->visibility);
    }

    /**
     * Test getting visibility status for multiple items.
     *
     * @covers ::get_items_visibility
     */
    public function test_get_items_visibility(): void {
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $category = $this->getDataGenerator()->create_category();

        // Set visibility for some items
        $items = [
            [
                'id' => 0,
                'itemid' => $course1->id,
                'itemtype' => item_type::COURSE->value,
                'visibility' => item_visibility::HIDDEN->value,
            ],
            [
                'id' => 0,
                'itemid' => $category->id,
                'itemtype' => item_type::CATEGORY->value,
                'visibility' => item_visibility::VISIBLE->value,
            ]
        ];

        item_visibility_api::set_items_visibility($items);

        // Query multiple items
        $queryItems = [
            ['itemid' => $course1->id, 'itemtype' => item_type::COURSE->value],
            ['itemid' => $course2->id, 'itemtype' => item_type::COURSE->value], // No record
            ['itemid' => $category->id, 'itemtype' => item_type::CATEGORY->value],
        ];

        $results = item_visibility_api::get_items_visibility($queryItems);

        // Should return 2 records (course1 and category)
        $this->assertCount(2, $results);
    }

    /**
     * Test checking if an item is visible.
     *
     * @covers ::is_item_visible
     */
    public function test_is_item_visible(): void {
        $course = $this->getDataGenerator()->create_course();

        // Item with no record should be visible by default
        $this->assertTrue(item_visibility_api::is_item_visible($course->id, item_type::COURSE->value));

        // Set item as hidden
        $items = [
            [
                'id' => 0,
                'itemid' => $course->id,
                'itemtype' => item_type::COURSE->value,
                'visibility' => item_visibility::HIDDEN->value,
            ]
        ];
        item_visibility_api::set_items_visibility($items);

        // Now it should be hidden
        $this->assertFalse(item_visibility_api::is_item_visible($course->id, item_type::COURSE->value));

        // Set item as visible
        $items[0]['visibility'] = item_visibility::VISIBLE->value;
        item_visibility_api::set_items_visibility($items);

        // Now it should be visible
        $this->assertTrue(item_visibility_api::is_item_visible($course->id, item_type::COURSE->value));
    }

    /**
     * Test updating existing visibility record.
     *
     * @covers ::set_items_visibility
     */
    public function test_update_existing_visibility(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        // First, create a visibility record
        $items = [
            [
                'id' => 0,
                'itemid' => $course->id,
                'itemtype' => item_type::COURSE->value,
                'visibility' => item_visibility::HIDDEN->value,
            ]
        ];
        $result = item_visibility_api::set_items_visibility($items);
        $recordId = $result['returneditems'][0]->id;

        // Verify initial state
        $record = $DB->get_record('local_resourcelibrary', ['id' => $recordId]);
        $this->assertEquals(item_visibility::HIDDEN->value, $record->visibility);

        // Update the visibility
        $items[0]['visibility'] = item_visibility::VISIBLE->value;
        item_visibility_api::set_items_visibility($items);

        // Verify the update
        $record = $DB->get_record('local_resourcelibrary', ['id' => $recordId]);
        $this->assertEquals(item_visibility::VISIBLE->value, $record->visibility);
    }

    /**
     * Test permission check for unauthorized user.
     *
     * @covers ::set_items_visibility
     */
    public function test_set_visibility_no_permission(): void {
        // Create a regular user without the required capability
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        $items = [
            [
                'id' => 0,
                'itemid' => $course->id,
                'itemtype' => item_type::COURSE->value,
                'visibility' => item_visibility::HIDDEN->value,
            ]
        ];

        $result = item_visibility_api::set_items_visibility($items);

        // Should return warning about permissions
        $this->assertNotEmpty($result['warnings']);
        $this->assertEquals('settingvisibilitynotallowed', $result['warnings'][0]['warningcode']);
        $this->assertEmpty($result['returneditems']);
    }

    /**
     * Test empty items array.
     *
     * @covers ::set_items_visibility
     * @covers ::get_items_visibility
     */
    public function test_empty_items_array(): void {
        $result = item_visibility_api::set_items_visibility([]);
        $this->assertEmpty($result['warnings']);
        $this->assertEmpty($result['returneditems']);

        $visibilityResults = item_visibility_api::get_items_visibility([]);
        $this->assertEmpty($visibilityResults);
    }

    /**
     * Create a structure of categories and courses based on the provided data.
     *
     * @param array $data
     * @return array
     */
    private function create_structure(array $data): array {
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
                $courses[$item['name']] = $dg->create_course([
                    'name' => $item['name'],
                    'category' => $categories[$item['category']]->id,
                ]);
            }
        }

        return ['categories' => $categories, 'courses' => $courses];
    }
}
