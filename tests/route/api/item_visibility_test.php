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
 * Unit tests for item_visibility REST API route.
 *
 * @package   local_resourcelibrary
 * @category  test
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\tests\route\api;

use advanced_testcase;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use local_resourcelibrary\route\api\item_visibility;
use local_resourcelibrary\tests\local_resourcelibrary_testcase;

/**
 * Unit tests for item_visibility REST API route.
 *
 * @package   local_resourcelibrary
 * @category  test
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \local_resourcelibrary\route\api\item_visibility
 */
class item_visibility_test extends local_resourcelibrary_testcase {

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test successful PUT request to update item visibility.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_success(): void {
        global $DB;

        // Create test data
        $course = $this->getDataGenerator()->create_course();

        // Create a visibility record
        $record = new \stdClass();
        $record->itemid = $course->id;
        $record->itemtype = 1; // Course type
        $record->visibility = 0; // Hidden
        $record->usermodified = 2;
        $record->timecreated = time();
        $record->timemodified = time();
        $recordid = $DB->insert_record('local_resourcelibrary', $record);

        // Create request
        $requestbody = json_encode([
            'itemtype' => 1,
            'visibility' => 1
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $course->id . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));
        $request = $request->withHeader('Content-Type', 'application/json');

        $response = new Response();
        $route = new item_visibility();

        // Execute the request
        $result = $route->handle_put($request, $response, $course->id);

        // Assert response
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertTrue($body['success']);
        $this->assertEquals($course->id, $body['itemid']);
        $this->assertEquals(1, $body['itemtype']);
        $this->assertEquals(1, $body['visibility']);

        // Verify database update
        $updated = $DB->get_record('local_resourcelibrary', ['id' => $recordid]);
        $this->assertEquals(1, $updated->visibility);
    }

    /**
     * Test PUT request with missing required fields.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_missing_fields(): void {
        $course = $this->getDataGenerator()->create_course();

        // Request with missing visibility field
        $requestbody = json_encode([
            'itemtype' => 1
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $course->id . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));
        $request = $request->withHeader('Content-Type', 'application/json');

        $response = new Response();
        $route = new item_visibility();

        // Execute the request
        $result = $route->handle_put($request, $response, $course->id);

        // Assert error response
        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertStringContainsString('Missing required fields', $body['error']);
    }

    /**
     * Test PUT request with invalid JSON body.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_invalid_json(): void {
        $course = $this->getDataGenerator()->create_course();

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $course->id . '/visibility');
        $request = $request->withParsedBody('invalid-json-string');

        $response = new Response();
        $route = new item_visibility();

        // Execute the request
        $result = $route->handle_put($request, $response, $course->id);

        // Assert error response
        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertStringContainsString('Invalid request body', $body['error']);
    }

    /**
     * Test PUT request for non-existent item.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_nonexistent_item(): void {
        $nonexistentid = 99999;

        $requestbody = json_encode([
            'itemtype' => 1,
            'visibility' => 1
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $nonexistentid . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));
        $request = $request->withHeader('Content-Type', 'application/json');

        $response = new Response();
        $route = new item_visibility();

        // Execute the request
        $result = $route->handle_put($request, $response, $nonexistentid);

        // Should return success even if item doesn't exist (creates new record)
        // This is based on how the external service works
        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * Test PUT request with category item type.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_category_item(): void {
        global $DB;

        // Create test category
        $category = $this->getDataGenerator()->create_category();

        $requestbody = json_encode([
            'itemtype' => 2, // Category type
            'visibility' => 0 // Hide
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $category->id . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));
        $request = $request->withHeader('Content-Type', 'application/json');

        $response = new Response();
        $route = new item_visibility();

        // Execute the request
        $result = $route->handle_put($request, $response, $category->id);

        // Assert response
        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertTrue($body['success']);
        $this->assertEquals($category->id, $body['itemid']);
        $this->assertEquals(2, $body['itemtype']);
        $this->assertEquals(0, $body['visibility']);

        // Verify database record
        $record = $DB->get_record('local_resourcelibrary', [
            'itemid' => $category->id,
            'itemtype' => 2
        ]);
        $this->assertNotFalse($record);
        $this->assertEquals(0, $record->visibility);
    }

    /**
     * Test PUT request without proper permissions.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_no_permissions(): void {
        // Create a regular user without manage visibility capability
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();

        $requestbody = json_encode([
            'itemtype' => 1,
            'visibility' => 1
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $course->id . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));
        $request = $request->withHeader('Content-Type', 'application/json');

        $response = new Response();
        $route = new item_visibility();

        // Execute the request
        $result = $route->handle_put($request, $response, $course->id);

        // Should return error due to lack of permissions
        // The external service throws an exception which results in 500
        $this->assertEquals(500, $result->getStatusCode());
        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $body);
    }

    /**
     * Test PUT request to toggle visibility from hidden to visible.
     *
     * @covers ::handle_put
     */
    public function test_handle_put_toggle_visibility(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        // Create initial hidden record
        $record = new \stdClass();
        $record->itemid = $course->id;
        $record->itemtype = 1;
        $record->visibility = 0; // Hidden
        $record->usermodified = 2;
        $record->timecreated = time();
        $record->timemodified = time();
        $recordid = $DB->insert_record('local_resourcelibrary', $record);

        // First request: make visible
        $requestbody = json_encode([
            'itemtype' => 1,
            'visibility' => 1
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $course->id . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));

        $route = new item_visibility();
        $result = $route->handle_put($request, new Response(), $course->id);

        $this->assertEquals(200, $result->getStatusCode());

        // Verify it's now visible
        $updated = $DB->get_record('local_resourcelibrary', ['id' => $recordid]);
        $this->assertEquals(1, $updated->visibility);

        // Second request: hide again
        $requestbody = json_encode([
            'itemtype' => 1,
            'visibility' => 0
        ]);

        $request = new ServerRequest('PUT', '/api/rest/v2/local_resourcelibrary/items/' . $course->id . '/visibility');
        $request = $request->withParsedBody(json_decode($requestbody, true));

        $result = $route->handle_put($request, new Response(), $course->id);

        $this->assertEquals(200, $result->getStatusCode());

        // Verify it's now hidden
        $updated = $DB->get_record('local_resourcelibrary', ['id' => $recordid]);
        $this->assertEquals(0, $updated->visibility);
    }
}
