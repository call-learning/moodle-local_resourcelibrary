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
namespace local_resourcelibrary\observer;

/**
 * Class eventmanager
 *
 * @package    local_resourcelibrary
 * @copyright  2023 CALL Learning- Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventmanager {
    /**
     * Course delete event observer.
     * This will delete any attached custom fields
     *
     * @param \core\event\course_deleted $event The course deleted event.
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        // Nothing happens here.
    }

    /**
     * Course create event observer.
     * On course creation a check should be made to see if any of its parent categories has the hidden flag set
     * in the local_resourcelibrary table. If so, we need to add a record to the local_resourcelibrary table for the
     * new course.
     * @param \core\event\course_created $event The course created event.
     */
    public static function course_created(\core\event\course_created $event) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/resourcelibrary/lib.php');
        $course = $event->get_record_snapshot('course', $event->objectid);
        // Check if the course category is hidden.
        $categorystatus = $DB->get_field('local_resourcelibrary', 'visibility',
            [
                'itemid' => $course->category,
                'itemtype' => LOCAL_RESOURCELIBRARY_ITEMTYPE_CATEGORY,
            ]);
        if ($categorystatus == LOCAL_RESOURCELIBRARY_ITEM_HIDDEN) {
            // Add a record for the course.
            $DB->insert_record('local_resourcelibrary',
                [
                    'itemid' => $course->id,
                    'itemtype' => LOCAL_RESOURCELIBRARY_ITEMTYPE_COURSE,
                    'visibility' => LOCAL_RESOURCELIBRARY_ITEM_HIDDEN,
                ]);
        }
    }
}
