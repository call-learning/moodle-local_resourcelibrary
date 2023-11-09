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
 * Control the visibility of items in the catalogue.
 *
 * @package   local_resourcelibrary
 * @copyright  2023 CALL Learning - Bas Brands bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\external;

use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use local_resourcelibrary\locallib\utils;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/resourcelibrary/lib.php');

/**
 * Class used for Ajax Management of the visibility of categories and courses
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_visibility extends external_api {

    /**
     * Returns description of method parameters
     *
     *
     * @return external_function_parameters
     */
    public static function set_items_visibility_parameters() {
        return new external_function_parameters(
            [
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'id'),
                            'itemid' => new external_value(PARAM_INT, 'item ID'),
                            'itemtype' => new external_value(PARAM_INT, 'item type'),
                            'visibility' => new external_value(PARAM_INT, 'visibility status'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Set the visibility status for items in the catalogue.
     * @param array $items
     */
    public static function set_items_visibility(array $items) {
        GLOBAL $DB;

        $params = self::validate_parameters(self::set_items_visibility_parameters(),
            [
                'items' => $items,
            ]
        );

        $warnings = [];
        $returneditems = [];

        // Check permissions for updating the catalogue.
        $context = \context_system::instance();
        if (!has_capability('local/resourcelibrary:setitemsvisibility', $context)) {
            $warnings[] = [
                'item' => $requestid,
                'warningcode' => 'settingvisibilitynotallowed',
                'message' => get_string('settingvisibilitynotallowed', 'local_resourcelibrary'),
            ];
        }

        foreach ($params['items'] as $item) {

            $warning = [];

            $item = (object)$item;
            $item->timemodified = time();

            // Check if the item exists.
            $sql = "SELECT id, visibility FROM {local_resourcelibrary} WHERE itemid = :itemid and itemtype = :itemtype";
            $params = ['itemid' => $item->itemid, 'itemtype' => $item->itemtype];
            $rlrecord = $DB->get_record_sql($sql, $params);
            if ($rlrecord) {
                $DB->update_record('local_resourcelibrary', $item);
            } else {
                $item->id = $DB->insert_record('local_resourcelibrary', $item);
            }
            // If the item is a category, we need to set the visibility of all courses and categories in this category.
            if ($item->itemtype == LOCAL_RESOURCELIBRARY_ITEMTYPE_CATEGORY) {
                $treeitems = self::get_category_tree($item->itemid, $item->visibility);
                foreach ($treeitems as $treeitem) {
                    $treeitem = (object)$treeitem;
                    $treeitem->timemodified = time();
                    $sql = "SELECT id, visibility FROM {local_resourcelibrary} WHERE itemid = :itemid and itemtype = :itemtype";
                    $params = ['itemid' => $treeitem->itemid, 'itemtype' => $treeitem->itemtype];
                    $rlrecord = $DB->get_record_sql($sql, $params);
                    if ($rlrecord) {
                        $treeitem->id = $rlrecord->id;
                        $DB->update_record('local_resourcelibrary', $treeitem);
                    } else {
                        $treeitem->id = $DB->insert_record('local_resourcelibrary', $treeitem);
                    }
                    $returneditems[] = $treeitem;
                }
            } else {
                $returneditems[] = $item;
            }
        }

        $result = [];
        $result['warnings'] = $warnings;
        $result['returneditems'] = $returneditems;
        return $result;
    }

    /**
     * Recursive function the create an array of catalogue items from a category its subcategories and all courses within.
     *
     * @param int $categoryid
     * @param int $visibility
     * @return array of items with id, itemid, itemtype and visibility
     */
    public static function get_category_tree($categoryid, $visibility) {
        GLOBAL $DB;

        $items = [];

        // Get all courses in this category.
        $courses = $DB->get_records('course', ['category' => $categoryid]);
        foreach ($courses as $course) {
            $items[] = [
                'itemid' => $course->id,
                'itemtype' => LOCAL_RESOURCELIBRARY_ITEMTYPE_COURSE,
                'visibility' => $visibility,
            ];
        }

        // Get all subcategories of this category.
        $subcategories = $DB->get_records('course_categories', ['parent' => $categoryid]);
        foreach ($subcategories as $subcategory) {
            // Recursively get the category tree for this subcategory.
            $subcategoryitems = self::get_category_tree($subcategory->id, $visibility);
            foreach ($subcategoryitems as $item) {
                $items[] = $item;
            }
        }

        // Add this category to the list of items.
        $items[] = [
            'itemid' => $categoryid,
            'itemtype' => LOCAL_RESOURCELIBRARY_ITEMTYPE_CATEGORY,
            'visibility' => $visibility,
        ];

        return $items;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function set_items_visibility_returns() {
        return new external_single_structure([
            'warnings' => new external_warnings(),
            'returneditems' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'id'),
                        'itemid' => new external_value(PARAM_INT, 'item ID'),
                        'itemtype' => new external_value(PARAM_INT, 'item type'),
                        'visibility' => new external_value(PARAM_INT, 'visibility status'),
                    ]
                )
            ),
        ]);
    }
}
