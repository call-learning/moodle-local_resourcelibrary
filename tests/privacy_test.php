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
 * Unit Tests for resourcelibrary privacy
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \core_privacy\local\request\writer;
use local_resourcelibrary\privacy\provider;

/**
 * Unit Tests for resourcelibrary privacy
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourcelibrary_privacy_testcase extends \core_privacy\tests\provider_testcase {
    /**
     * Ensure that export_user_preferences returns no data if the user has not visited the library page.
     * @throws dml_exception
     */
    public function test_export_user_preferences_no_pref() {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test the export_user_preferences given different inputs
     *
     * @param string $type The name of the user preference to get/set
     * @param string $value The value you are storing
     *
     * @param string $expected
     * @throws coding_exception
     * @throws dml_exception
     * @dataProvider user_preference_provider
     */
    public function test_export_user_preferences($type, $value, $expected) {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        set_user_preference($type, $value, $user);
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $preferences = $writer->get_user_preferences('local_resourcelibrary');
        if (!$expected) {
            $expected = get_string($value, 'local_resourcelibrary');
        }
        $this->assertEquals($expected, $preferences->{$type}->value);
    }

    /**
     * Create an array of valid user preferences for the library page.
     *
     * @return array Array of valid user preferences.
     */
    public function user_preference_provider() {
        return array(
            array('local_resourcelibrary_user_sort_preference', 'title', ''),
            array('local_resourcelibrary_user_view_preference', 'card', ''),
            array('local_resourcelibrary_user_view_preference', 'list', ''),
            array('local_resourcelibrary_user_paging_preference', 12, 12)
        );
    }
}
