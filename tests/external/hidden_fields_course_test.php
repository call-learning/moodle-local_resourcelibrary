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
use local_resourcelibrary\external\get_filtered_courses;
use local_resourcelibrary\external\get_hidden_fields;
use local_resourcelibrary\external\hide_fields_filter;
use local_resourcelibrary\external\show_field_filter;
use local_resourcelibrary\tests\local_resourcelibrary_testcase;

/**
 * Tests for get_filtered_course_test static functions
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_resourcelibrary\external\get_hidden_fields::execute
 * @covers \local_resourcelibrary\external\hide_fields_filter::execute
 * @covers \local_resourcelibrary\external\show_field_filter::execute
 * @runTestsInSeparateProcesses
 */
final class hidden_fields_course_test extends local_resourcelibrary_testcase {
    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_hidden_fields(...$params) {
        $gethiddenfields = get_hidden_fields::execute(...$params);

        return external_api::clean_returnvalue(get_hidden_fields::execute_returns(), $gethiddenfields);
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     */
    protected function hide_fields_filter(...$params): void {
        hide_fields_filter::execute(...$params);
    }


    /**
     * Helper
     *
     * @param mixed ...$params
     */
    protected function show_field_filter(...$params): void {
        show_field_filter::execute(...$params);
    }

    /**
     * Test that we can hide and show fields in the filter
     */
    public function test_hide_and_show_filters(): void {
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
        // Initially no hidden field.
        $hiddenfields = $this->get_hidden_fields('core_course', 'course');
        $this->assertEmpty($hiddenfields);
        // Hide field f1.
        $this->hide_fields_filter('core_course', 'course', ['f1']);
        $hiddenfields = $this->get_hidden_fields('core_course', 'course');
        $this->assertCount(1, $hiddenfields);
        $this->assertEquals('f1', $hiddenfields[0]['shortname']);
        // Hide field f5 too.
        $this->hide_fields_filter('core_course', 'course', ['f5']);
        $hiddenfields = $this->get_hidden_fields('core_course', 'course');
        $this->assertCount(2, $hiddenfields);
        $this->assertEquals('f1', $hiddenfields[0]['shortname']);
        $this->assertEquals('f5', $hiddenfields[1]['shortname']);
        // Show field f1.
        $this->show_field_filter('core_course', 'course', ['f1']);
        $hiddenfields = $this->get_hidden_fields('core_course', 'course');
        $this->assertCount(1, $hiddenfields);
        $this->assertEquals('f5', $hiddenfields[0]['shortname']);
        // Show field f5.
        $this->show_field_filter('core_course', 'course', ['f5']);
        $hiddenfields = $this->get_hidden_fields('core_course', 'course');
        $this->assertEmpty($hiddenfields);
    }
}
