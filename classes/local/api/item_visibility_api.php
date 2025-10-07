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
 * Item visibility management API.
 *
 * @package   local_resourcelibrary
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\local\api;

use context_system;
use local_resourcelibrary\item_type;
use local_resourcelibrary\item_visibility;

/**
 * API for managing item visibility in the resource library catalogue.
 *
 * @package   local_resourcelibrary
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_visibility_api {

    /**
     * Set the visibility status for items in the catalogue.
     *
     * @param array $items Array of items with id, itemid, itemtype, and visibility
     * @return array Result array with warnings and returned items
     * @throws \required_capability_exception
     * @throws \invalid_parameter_exception
     */
    public static function set_items_visibility(array $items): array {
        global $DB;

        $warnings = [];
        $returneditems = [];

        // Check permissions for updating the catalogue.
        $context = context_system::instance();
        if (!has_capability('local/resourcelibrary:setitemsvisibility', $context)) {
            $warnings[] = [
                'itemid' => 0,
                'warningcode' => 'settingvisibilitynotallowed',
                'message' => get_string('settingvisibilitynotallowed', 'local_resourcelibrary'),
            ];
            return [
                'warnings' => $warnings,
                'returneditems' => $returneditems,
            ];
        }

        foreach ($items as $item) {
            $item = (object) $item;
            $item->timemodified = time();

            // Check if the item exists.
            $sql = "SELECT id, visibility FROM {local_resourcelibrary} WHERE itemid = :itemid AND itemtype = :itemtype";
            $params = ['itemid' => $item->itemid, 'itemtype' => $item->itemtype];
            $rlrecord = $DB->get_record_sql($sql, $params);

            if ($rlrecord) {
                $item->id = $rlrecord->id;
                $DB->update_record('local_resourcelibrary', $item);
            } else {
                $item->id = $DB->insert_record('local_resourcelibrary', $item);
            }

            // If the item is a category, we need to set the visibility of all courses and categories in this category.
            if ($item->itemtype == item_type::CATEGORY->value) {
                $treeitems = self::get_category_tree($item->itemid, $item->visibility);
                foreach ($treeitems as $treeitem) {
                    $treeitem = (object) $treeitem;
                    $treeitem->timemodified = time();
                    $sql = "SELECT id, visibility FROM {local_resourcelibrary} WHERE itemid = :itemid AND itemtype = :itemtype";
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

        return [
            'warnings' => $warnings,
            'returneditems' => $returneditems,
        ];
    }

    /**
     * Get the visibility status of a single item.
     *
     * @param int $itemid The item ID
     * @param int $itemtype The item type
     * @return \stdClass|null The visibility record or null if not found
     */
    public static function get_item_visibility(int $itemid, int $itemtype): ?\stdClass {
        global $DB;

        $sql = "SELECT id, itemid, itemtype, visibility FROM {local_resourcelibrary}
                WHERE itemid = :itemid AND itemtype = :itemtype";
        $params = ['itemid' => $itemid, 'itemtype' => $itemtype];

        return $DB->get_record_sql($sql, $params) ?: null;
    }

    /**
     * Get visibility status for multiple items.
     *
     * @param array $items Array of items with itemid and itemtype
     * @return array Array of visibility records
     */
    public static function get_items_visibility(array $items): array {
        global $DB;

        if (empty($items)) {
            return [];
        }

        $conditions = [];
        $params = [];

        foreach ($items as $index => $item) {
            $conditions[] = "(itemid = :itemid{$index} AND itemtype = :itemtype{$index})";
            $params["itemid{$index}"] = $item['itemid'];
            $params["itemtype{$index}"] = $item['itemtype'];
        }

        $sql = "SELECT id, itemid, itemtype, visibility FROM {local_resourcelibrary}
                WHERE " . implode(' OR ', $conditions);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Check if an item is visible in the catalogue.
     *
     * @param int $itemid The item ID
     * @param int $itemtype The item type
     * @return bool True if visible, false if hidden
     */
    public static function is_item_visible(int $itemid, int $itemtype): bool {
        $record = self::get_item_visibility($itemid, $itemtype);

        // If no record exists, item is visible by default
        if (!$record) {
            return true;
        }

        // VISIBLE = 0, HIDDEN = 1, so we need to check if visibility equals VISIBLE
        return (int) $record->visibility === item_visibility::VISIBLE->value;
    }

    /**
     * Recursive function to create an array of catalogue items from a category,
     * its subcategories and all courses within.
     *
     * @param int $categoryid The category ID
     * @param int $visibility The visibility status to apply
     * @return array Array of items with itemid, itemtype and visibility
     */
    protected static function get_category_tree(int $categoryid, int $visibility): array {
        global $DB;

        $items = [];

        // Get all courses in this category.
        $courses = $DB->get_records('course', ['category' => $categoryid]);
        foreach ($courses as $course) {
            $items[] = [
                'itemid' => $course->id,
                'itemtype' => item_type::COURSE->value,
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
            'itemtype' => item_type::CATEGORY->value,
            'visibility' => $visibility,
        ];

        return $items;
    }
}
