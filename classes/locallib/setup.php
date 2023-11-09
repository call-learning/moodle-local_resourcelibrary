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
 * Internal function and routine for the plugin
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\locallib;

use context_system;
use core_customfield\category;
use core_customfield\category_controller;
use stdClass;

/**
 * Class setup
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setup {
    /**
     * Generic category short name
     */
    const GENERIC_CATEGORIES = ['general'];

    /**
     * Create or update the first category of each custom field types
     *
     * @throws \coding_exception
     */
    public static function setup_resourcelibrary_custom_fields() {
        foreach (self::GENERIC_CATEGORIES as $catshorname) {
            $fullname = get_string('category:' . $catshorname, 'local_resourcelibrary');
            $carea = 'coursemodule'; // Just add the module resourcelibrary fields.
            $categories = category::get_records(['component' => 'local_resourcelibrary', 'area' => $carea]);
            if (empty($categories)) {
                $categorydata = new stdClass();
                $categorydata->name = $fullname;
                $categorydata->component = 'local_resourcelibrary';
                $categorydata->area = 'coursemodule'; // Just add the module resourcelibrary fields.
                $categorydata->itemid = 0;
                $categorydata->contextid = context_system::instance()->id;
                $category = category_controller::create(0, $categorydata);
                $category->save();
            } else {
                // Reset the first category name.
                $firstcat = reset($categories);
                $firstcat->set('name', $fullname);
                $firstcat->save();
            }
        }

    }
}
