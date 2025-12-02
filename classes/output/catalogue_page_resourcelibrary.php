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
 * Catalogue page resourcelibrary
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;

use local_resourcelibrary\local\api\course_filter_api;
use local_resourcelibrary\local\persistent\catalogue_page;
use renderer_base;

/**
 * Class containing data for a specific catalogue page resourcelibrary.
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalogue_page_resourcelibrary extends base_resourcelibrary {
    /**
     * The catalogue page
     *
     * @var catalogue_page
     */
    protected $cataloguepage;

    /**
     * Constructor
     *
     * @param catalogue_page $cataloguepage The catalogue page to display
     * @param string $sort Sort user preference
     * @param string $view Display user preference
     * @param int $paging Paging size
     */
    public function __construct(
        catalogue_page $cataloguepage,
        string $sort = self::SORT_FULLNAME_ASC,
        string $view = self::VIEW_CARD,
        int $paging = self::PAGING_12,
    ) {
        parent::__construct($sort, $view, $paging);
        $this->cataloguepage = $cataloguepage;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        $handler = \core_course\customfield\course_handler::create();

        // Get the base export data.
        $defaultvariables = $this->get_export_defaults($output, $handler, $this->cataloguepage->get('id'));
        $defaultvariables['parentid'] = 0;
        $defaultvariables['categoryid'] = 0;
        $defaultvariables['pageid'] = $this->cataloguepage->get('id');

        // Add catalogue page specific data.
        $defaultvariables['cataloguepage'] = [
            'id' => $this->cataloguepage->get('id'),
            'name' => $this->cataloguepage->get('name'),
        ];

        // Get filtered courses with pagination data.
        $coursedata = $this->get_filtered_courses();
        $filteredcourses = $coursedata['courses'];
        $totalitems = $coursedata['totalItems'];

        $defaultvariables['hascourses'] = !empty($filteredcourses);
        $defaultvariables['entities'] = $filteredcourses;
        $defaultvariables['totalItems'] = $totalitems;
        $defaultvariables['currentPage'] = 1;
        $defaultvariables['itemsPerPage'] = $this->paging;

        // Add view-specific data.
        $defaultvariables['view_cards'] = ($this->view === self::VIEW_CARD);
        $defaultvariables['view_list'] = ($this->view === self::VIEW_LIST);

        // Add pagination data if needed.
        $totalpages = ceil($totalitems / $this->paging);
        $defaultvariables['showPagination'] = $totalpages > 1;
        $defaultvariables['totalPages'] = $totalpages;

        $preferences = $this->get_preferences();
        return array_merge($defaultvariables, $preferences);
    }

    /**
     * Get the courses filtered by the catalogue page criteria
     *
     * @return array Array with 'courses' and 'totalItems' keys
     */
    protected function get_filtered_courses() {
        // Get all courses first to count total.
        $allcourses = course_filter_api::get_filtered_courses(
            $this->cataloguepage->get('id'), // Pageid - catalogue page ID.
            [], // Filters - can be extended to support runtime filtering.
            0, // Limit - no limit to get total count.
            0, // Offset - no pagination.
            []  // Sorting - default sorting.
        );

        // Limit to first 12 items for initial render.
        $limitedcourses = array_slice($allcourses, 0, $this->paging);

        return [
            'courses' => $limitedcourses,
            'totalItems' => count($allcourses),
        ];
    }
}
