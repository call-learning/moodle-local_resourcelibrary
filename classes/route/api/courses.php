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

namespace local_resourcelibrary\route\api;

use core\param;
use core\router\route;
use core\router\schema\parameters\query_parameter;
use core\router\schema\response\payload_response;

use local_resourcelibrary\local\api\course_filter_api;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Resource Library Courses API
 *
 * Provides REST endpoints for accessing filtered course data from the resource library.
 * This maintains all the performance optimizations from the original external API
 * while providing modern REST capabilities.
 *
 * @package    local_resourcelibrary
 * @copyright  2025 CALL Learning 2025 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[route(
    path: '/courses',
)]
class courses {

    /**
     * Get filtered courses from the resource library
     *
     * Returns a paginated list of courses with filtering, sorting, and visibility checks.
     * Maintains all performance optimizations from the original implementation.
     *
     * @param ServerRequestInterface $request The HTTP request
     * @param ResponseInterface $response The HTTP response
     * @param int $categoryid Category ID to filter by (0 for all categories)
     * @param int $limit Maximum number of courses to return (0 for no limit)
     * @param int $offset Number of courses to skip for pagination
     * @return payload_response
     */
    #[route(
        method: 'GET',
        title: 'Get filtered courses',
        description: 'Retrieve filtered and paginated course list from the resource library with custom field support',
        queryparams: [
            new query_parameter(
                name: 'pageid',
                type: param::INT,
                description: 'Catalogue page ID to filter by (0 for all courses)',
                required: false,
            ),
            new query_parameter(
                name: 'categoryid',
                type: param::INT,
                description: 'Legacy category ID to filter courses (0 for all categories)',
                required: false,
            ),
            new query_parameter(
                name: 'limit',
                type: param::INT,
                description: 'Maximum number of courses to return (0 for no limit)',
                required: false,
            ),
            new query_parameter(
                name: 'offset',
                type: param::INT,
                description: 'Number of courses to skip for pagination',
                required: false,
            ),
            new query_parameter(
                name: 'filters',
                type: param::RAW,
                description: 'JSON encoded array of custom field filters',
                required: false,
            ),
            new query_parameter(
                name: 'sorting',
                type: param::RAW,
                description: 'JSON encoded array of sorting options',
                required: false,
            ),
        ],
    )]
    public function get_courses(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $pageid = 0,
        int $categoryid = 0,
        int $limit = 0,
        int $offset = 0,
    ): payload_response {
        // Parse query parameters
        $queryparams = $request->getQueryParams();

        // Override URL parameters with query parameters if provided
        $pageid = isset($queryparams['pageid']) ? (int) $queryparams['pageid'] : $pageid;
        $categoryid = isset($queryparams['categoryid']) ? (int) $queryparams['categoryid'] : $categoryid;
        $limit = isset($queryparams['limit']) ? (int) $queryparams['limit'] : $limit;
        $offset = isset($queryparams['offset']) ? (int) $queryparams['offset'] : $offset;

        // Parse filters from JSON string if provided
        $filters = [];
        if (!empty($queryparams['filters'])) {
            $filters = json_decode($queryparams['filters'], true) ?? [];
        }

        // Parse sorting from JSON string if provided
        $sorting = [];
        if (!empty($queryparams['sorting'])) {
            $sorting = json_decode($queryparams['sorting'], true) ?? [];
        }

        // Use the new API to get filtered courses
        $result = course_filter_api::get_filtered_courses($pageid, $filters, $limit, $offset, $sorting, $categoryid);

        return new payload_response($result, $request, $response);
    }
}
