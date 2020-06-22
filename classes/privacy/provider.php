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
 * Course resourcelibrary
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\privacy;

use core_privacy\local\request\user_preference_provider;
use core_privacy\local\metadata\collection;
use \core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for local_resourcelibrary.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, user_preference_provider {

    /**
     * Returns meta-data information about the resourcelibrary plugin.
     *
     * @param  \core_privacy\local\metadata\collection $collection A collection of meta-data.
     * @return \core_privacy\local\metadata\collection Return the collection of meta-data.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('local_resourcelibrary_sort_preference', 'privacy:metadata:resourcelibrarysortpreference');
        $collection->add_user_preference('local_resourcelibrary_view_preference', 'privacy:metadata:resourcelibraryviewpreference');
        $collection->add_user_preference('local_resourcelibrary_user_paging_preference',
            'privacy:metadata:resourcelibrarypagingpreference');
        return $collection;
    }
    /**
     * Export all user preferences for the myoverview block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('local_resourcelibrary_user_sort_preference', null, $userid);
        if (isset($preference)) {
            writer::export_user_preference('local_resourcelibrary',
                'local_resourcelibrary_user_sort_preference', get_string($preference, 'local_resourcelibrary'),
                get_string('privacy:metadata:resourcelibrarysortpreference', 'local_resourcelibrary'));
        }

        $preference = get_user_preferences('local_resourcelibrary_user_view_preference', null, $userid);
        if (isset($preference)) {
            writer::export_user_preference('local_resourcelibrary',
                'local_resourcelibrary_user_view_preference',
                get_string($preference, 'local_resourcelibrary'),
                get_string('privacy:metadata:resourcelibraryviewpreference', 'local_resourcelibrary'));
        }

        $preference = get_user_preferences('local_resourcelibrary_user_paging_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('local_resourcelibrary',
                'local_resourcelibrary_user_paging_preference',
                $preference,
                get_string('privacy:metadata:resourcelibrarypagingpreference', 'local_resourcelibrary'));
        }
    }
}