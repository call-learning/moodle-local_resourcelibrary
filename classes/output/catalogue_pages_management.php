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
 * Catalogue pages management renderable
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;

use local_resourcelibrary\local\persistent\catalogue_page;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * Catalogue pages management renderable
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalogue_pages_management implements renderable, templatable {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;

        $pages = catalogue_page::get_all_pages();
        $pagedata = [];

        foreach ($pages as $page) {
            $categoriesarray = $page->get_categories_array();
            $customfieldsarray = $page->get_customfields_array();

            // Format categories for display
            $categoriestext = '';
            if (!empty($categoriesarray)) {
                $categorynames = [];
                foreach ($categoriesarray as $categoryid) {
                    if ($category = \core_course_category::get($categoryid, IGNORE_MISSING)) {
                        $categorynames[] = $category->get_formatted_name();
                    }
                }
                $categoriestext = implode(', ', $categorynames);
            }

            // Format custom fields for display
            $customfieldstext = '';
            if (!empty($customfieldsarray)) {
                $customfieldstext = implode(', ', $customfieldsarray);
            }

            $viewurl = new moodle_url('/local/resourcelibrary/page.php', ['id' => $page->get('id')]);
            $editurl = new moodle_url('/local/resourcelibrary/edit.php', ['id' => $page->get('id')]);
            $deleteurl = new moodle_url('/local/resourcelibrary/edit.php', [
                'id' => $page->get('id'),
                'action' => 'delete',
                'sesskey' => sesskey()
            ]);

            $pagedata[] = [
                'id' => $page->get('id'),
                'name' => $page->get('name'),
                'categories' => $categoriestext ?: get_string('none'),
                'customfields' => $customfieldstext ?: get_string('none'),
                'timemodified' => userdate($page->get('timemodified')),
                'viewurl' => $viewurl->out(false),
                'editurl' => $editurl->out(false),
                'deleteurl' => $deleteurl->out(false),
            ];
        }

        $addurl = is_siteadmin() ? new moodle_url('/local/resourcelibrary/edit.php') : null;

        return [
            'pages' => $pagedata,
            'addurl' => $addurl ? $addurl->out(false) : null,
            'haspages' => !empty($pagedata),
        ];
    }
}
