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
 * Catalogue page persistent class
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\local\persistent;

use core\persistent;

/**
 * Catalogue page persistent class
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalogue_page extends persistent {
    /**
     * Current table
     */
    const TABLE = 'local_resourcelibrary_pages';

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'name' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'categories' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'customfields' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
        ];
    }

    /**
     * Get the categories as an array
     *
     * @return array
     */
    public function get_categories_array(): array {
        $categories = $this->get('categories');
        if (empty($categories)) {
            return [];
        }
        $decoded = json_decode($categories, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set the categories from an array
     *
     * @param array $categories
     */
    public function set_categories_array(array $categories): void {
        $this->set('categories', json_encode(array_values($categories)));
    }

    /**
     * Get the custom fields as an array
     *
     * @return array
     */
    public function get_customfields_array(): array {
        $customfields = $this->get('customfields');
        if (empty($customfields)) {
            return [];
        }
        $decoded = json_decode($customfields, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set the custom fields from an array
     *
     * @param array $customfields
     */
    public function set_customfields_array(array $customfields): void {
        $this->set('customfields', json_encode(array_values($customfields)));
    }

    /**
     * Validate categories field
     *
     * @param string $value
     * @return true|lang_string
     */
    protected function validate_categories($value) {
        if ($value === null || $value === '') {
            return true;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON data';
        }

        if (!is_array($decoded)) {
            return 'Data must be a JSON array';
        }

        return true;
    }

    /**
     * Validate customfields field
     *
     * @param string $value
     * @return true|lang_string
     */
    protected function validate_customfields($value) {
        if ($value === null || $value === '') {
            return true;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON data';
        }

        if (!is_array($decoded)) {
            return 'Data must be a JSON array';
        }

        return true;
    }

    /**
     * Get all catalogue pages ordered by name
     *
     * @return array
     */
    public static function get_all_pages(): array {
        return self::get_records([], 'name');
    }

    /**
     * Get catalogue page by ID with formatted data for display
     *
     * @param int $id
     * @return array|null
     */
    public static function get_page_for_display(int $id): ?array {
        $page = self::get_record(['id' => $id]);
        if (!$page) {
            return null;
        }

        return [
            'id' => $page->get('id'),
            'name' => $page->get('name'),
            'categories' => $page->get_categories_array(),
            'customfields' => $page->get_customfields_array(),
            'timecreated' => $page->get('timecreated'),
            'timemodified' => $page->get('timemodified'),
        ];
    }
}
