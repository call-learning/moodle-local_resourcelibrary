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
 * REST API route for item visibility management.
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
use local_resourcelibrary\local\api\item_visibility_api;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

/**
 * REST API route for item visibility management.
 */
#[route(
    path: '/items/{itemid}/visibility',
)]
class item_visibility {

    /**
     * Handle PUT request for updating item visibility.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    #[route(
        title: 'Update item visibility',
        description: 'Update the visibility status of an item in the catalog',
        method: 'PUT',
        summary: 'Update item visibility',
        pathtypes: [
            new path_parameter(
                name: 'itemid',
                type: param::INT,
                description: 'The ID of the item to update'
            ),
        ],
        requestbody: new request_body(
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'itemtype' => new scalar_type(param::INT, required: true),
                        'visibility' => new scalar_type(param::INT, required: true),
                    ],
                ),
            ),
            required: true,
        ),
    )]
    public function handle_put(ServerRequestInterface $request, ResponseInterface $response, int $itemid): ResponseInterface {
        global $USER;

        // Get parsed request body (handled by PSR-7 middleware)
        $data = $request->getParsedBody();

        if (!is_array($data)) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'Invalid request body - must be JSON object']));
        }

        // Validate required fields
        if (!isset($data['itemtype']) || !isset($data['visibility'])) {
            return new Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'Missing required fields: itemtype, visibility']));
        }

        $itemtype = (int) $data['itemtype'];
        $visibility = (int) $data['visibility'];

        // Prepare data for external service
        $items = [
            'items' => [
                [
                    'id' => $itemid, // This might need to be different from itemid
                    'itemid' => $itemid,
                    'itemtype' => $itemtype,
                    'visibility' => $visibility
                ]
            ]
        ];

        try {
            // Use the new API directly
            $result = item_visibility_api::set_items_visibility($items['items']);

            if (!empty($result['warnings'])) {
                return new Response(403, ['Content-Type' => 'application/json'],
                    json_encode(['error' => $result['warnings'][0]['message'] ?? 'Unknown error']));
            }

            if (!empty($result['returneditems'])) {
                $item = $result['returneditems'][0];
                return new Response(200, ['Content-Type' => 'application/json'],
                    json_encode([
                        'success' => true,
                        'itemid' => $item->itemid,
                        'itemtype' => $item->itemtype,
                        'visibility' => $item->visibility
                    ]));
            }

            return new Response(500, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'Failed to update item visibility']));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'],
                json_encode(['error' => $e->getMessage()]));
        }
    }
}
