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
 * Resource Library functions and service definitions.
 *
 * @package    local_resourcelibrary
 * @category   webservice
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(
    'local_resourcelibrary_get_filtered_courses' => array(
        'classname' => 'local_resourcelibrary_external',
        'methodname' => 'get_filtered_courses',
        'classpath' => 'local/resourcelibrary/externallib.php',
        'description' => 'Return a list of filtered courses course details',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'loginrequired' => false // Global filter course page is accessible without being logged in.
    ),
    'local_resourcelibrary_get_filtered_course_content' => array(
        'classname' => 'local_resourcelibrary_external',
        'methodname' => 'get_filtered_course_content',
        'classpath' => 'local/resourcelibrary/externallib.php',
        'description' => 'Return a list of filtered activities in a given course',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'loginrequired' => false // Global filter course page is accessible without being logged in.
    ),
    'local_resourcelibrary_hide_fields_filters' => array(
        'classname' => '\\local_resourcelibrary\\external\\manage_customfields',
        'methodname' => 'hide_fields_filter',
        'description' => 'Hide a set of fields from the filters',
        'type' => 'write',
        'capabilities' => 'local/resourcelibrary:configurecustomfields',
        'ajax' => true,
        'loginrequired' => true
    ),
    'local_resourcelibrary_show_fields_filters' => array(
        'classname' => '\\local_resourcelibrary\\external\\manage_customfields',
        'methodname' => 'show_fields_filter',
        'description' => 'Make sure that the given set of fields will show in the filters',
        'type' => 'write',
        'capabilities' => 'local/resourcelibrary:configurecustomfields',
        'ajax' => true,
        'loginrequired' => true
    ),
    'local_resourcelibrary_get_hidden_fields_filters' => array(
        'classname' => '\\local_resourcelibrary\\external\\manage_customfields',
        'methodname' => 'get_hidden_fields_filters',
        'description' => 'Get the list of filters that are hidden',
        'type' => 'read',
        'capabilities' => 'local/resourcelibrary:configurecustomfields',
        'ajax' => true,
        'loginrequired' => true
    ),
);
