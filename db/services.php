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

$functions = [
    'local_resourcelibrary_get_hidden_fields_filters' => [
        'classname' => \local_resourcelibrary\external\get_hidden_fields::class,
        'methodname' => 'execute',
        'description' => 'Get the list of filters that are hidden',
        'type' => 'read',
        'capabilities' => 'local/resourcelibrary:configurecustomfields',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
