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
 * Resource Library test utills
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function generate_category_and_fields($dg) {
    $generator = $dg->get_plugin_generator('local_resourcelibrary');
    foreach (array('course', 'coursemodule') as $type) {
        $catid = $generator->create_category(['area' => $type])->get('id');
        $generator->create_field(['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1', 'area' => $type]);
        $generator->create_field(['categoryid' => $catid, 'type' => 'checkbox', 'shortname' => 'f2', 'area' => $type]);
        $generator->create_field(['categoryid' => $catid, 'type' => 'date', 'shortname' => 'f3',
            'configdata' => ['startyear' => 2000, 'endyear' => 3000, 'includetime' => 1], 'area' => $type]);
        $generator->create_field(['categoryid' => $catid, 'type' => 'select', 'shortname' => 'f4',
            'configdata' => ['options' => "a\nb\nc"], 'area' => $type]);
        $dg->create_custom_field(['categoryid' => $catid, 'type' => 'textarea', 'shortname' => 'f5', 'area' => $type]);
    }
}

/**
 * Standard setup for resourcelibrary tests
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class local_resourcelibrary_testcase extends advanced_testcase {
    /**
     * Set up
     */
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $dg = self::getDataGenerator();
        generate_category_and_fields($dg);

    }
}