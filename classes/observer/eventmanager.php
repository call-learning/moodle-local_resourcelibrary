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
 * Course handler for metadata fields trait
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_resourcelibrary\observer;

defined('MOODLE_INTERNAL') || die();
use local_resourcelibrary\customfield\course_handler;
use local_resourcelibrary\customfield\coursemodule_handler;

/**
 * Class eventmanager
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
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
        $handler = course_handler::create();
        $handler->delete_instance($event->courseid);

    }

    /**
     * Course Module delete event observer.
     * This will delete any attached custom fields
     *
     * @param \core\event\course_module_deleted $event The course deleted event.
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        $handler = coursemodule_handler::create();
        $handler->delete_instance($event->objectid);
    }
}