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
 * REST API route for custom field filter management.
 *
 * @package   local_resourcelibrary
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\route\api;

use core\param;
use core\router\route;
use core\router\schema\parameters\path_parameter;
use core\router\schema\request_body;
use core\router\schema\response\content\payload_response_type;
use core\router\schema\objects\schema_object;
use core\router\schema\objects\scalar_type;
use core\router\schema\objects\array_of_strings;
use local_resourcelibrary\external\hide_fields_filter;
use local_resourcelibrary\external\show_field_filter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

/**
 * REST API route for custom field filter management.
 */
#[route(
    path: '/filters/{component}/{area}',
)]
class filters {

    /**
     * Hide custom field filters.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $component
     * @param string $area
     * @return ResponseInterface
     */
    #[route(
        title: 'Hide custom field filters',
        description: 'Hide specified custom fields from the filter list',
        method: 'DELETE',
        summary: 'Hide custom field filters',
        pathtypes: [
            new path_parameter(
                name: 'component',
                type: param::ALPHANUMEXT,
                description: 'Custom field handler component (e.g., core_course)'
            ),
            new path_parameter(
                name: 'area',
                type: param::ALPHANUMEXT,
                description: 'Custom field handler area (e.g., course)'
            ),
        ],
        requestbody: new request_body(
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'fieldshortnames' => new array_of_strings(
                            keyparamtype: param::INT,
                            valueparamtype: param::ALPHANUMEXT,
                        ),
                    ],
                ),
            ),
            required: true,
        ),
    )]
    public function hide_filters(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $component,
        string $area
    ): ResponseInterface {
        // Get parsed request body
        $data = $request->getParsedBody();

        if (!is_array($data) || !isset($data['fieldshortnames'])) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'Missing required field: fieldshortnames']));
        }

        $fieldshortnames = $data['fieldshortnames'];

        if (!is_array($fieldshortnames)) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'fieldshortnames must be an array']));
        }

        try {
            // Use existing external service
            hide_fields_filter::execute($component, $area, $fieldshortnames);

            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode([
                    'success' => true,
                    'message' => 'Custom field filters hidden successfully',
                    'component' => $component,
                    'area' => $area,
                    'hidden_fields' => $fieldshortnames
                ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'],
                json_encode(['error' => $e->getMessage()]));
        }
    }

    /**
     * Show custom field filters.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $component
     * @param string $area
     * @return ResponseInterface
     */
    #[route(
        title: 'Show custom field filters',
        description: 'Make specified custom fields visible in the filter list',
        method: 'POST',
        summary: 'Show custom field filters',
        pathtypes: [
            new path_parameter(
                name: 'component',
                type: param::ALPHANUMEXT,
                description: 'Custom field handler component (e.g., core_course)'
            ),
            new path_parameter(
                name: 'area',
                type: param::ALPHANUMEXT,
                description: 'Custom field handler area (e.g., course)'
            ),
        ],
        requestbody: new request_body(
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'fieldshortnames' => new array_of_strings(
                            keyparamtype: param::INT,
                            valueparamtype: param::ALPHANUMEXT,
                        ),
                    ],
                ),
            ),
            required: true,
        ),
    )]
    public function show_filters(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $component,
        string $area
    ): ResponseInterface {
        // Get parsed request body
        $data = $request->getParsedBody();

        if (!is_array($data) || !isset($data['fieldshortnames'])) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'Missing required field: fieldshortnames']));
        }

        $fieldshortnames = $data['fieldshortnames'];

        if (!is_array($fieldshortnames)) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'fieldshortnames must be an array']));
        }

        try {
            // Use existing external service
            show_field_filter::execute($component, $area, $fieldshortnames);

            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode([
                    'success' => true,
                    'message' => 'Custom field filters shown successfully',
                    'component' => $component,
                    'area' => $area,
                    'shown_fields' => $fieldshortnames
                ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'],
                json_encode(['error' => $e->getMessage()]));
        }
    }
}
