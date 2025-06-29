<?php
namespace Roger;
require_once(dirname(__FILE__) . '/../src/Loader.php');

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class TranslationUnitControllerTest extends TestCase
{
    private Client $client;

    /**
     * Set up a Guzzle client for simulating HTTP requests.
     *
     * This method runs before each test and initializes the Guzzle client to simulate HTTP requests
     * to the API endpoint for testing.
     */
    protected function setUp(): void
    {
        // Set up a client instance to simulate HTTP requests
        $this->client = new Client([
            'base_uri' => 'http://localhost:8000/api/', // The base URL of your API
        ]);
    }

    /**
     * Test fetching translation units via the GET API endpoint.
     *
     * This test sends a GET request to the API to fetch translation units and verifies the
     * response contains the expected data and status code 200.
     */
    public function testGetTranslationUnits()
    {
        $response = $this->client->get('translation-units');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $data);
        $this->assertGreaterThan(0, count($data['data']));
    }

    /**
     * Test creating a new translation unit via the POST API endpoint.
     *
     * This test sends a POST request to the API with a new translation unit and verifies that
     * the response returns a success status and contains the created unit's data.
     */
    public function testCreateTranslationUnit()
    {
        $response = $this->client->post('translation-units', [
            'json' => [
                'source_text' => 'Hello, world!',
                'language_id' => 1,
                'status' => 'untranslated',
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
    }

    /**
     * Test updating an existing translation unit via the PUT API endpoint.
     *
     * This test sends a PUT request to the API to update an existing translation unit's source text
     * and status, then verifies that the response returns a success status and the correct message.
     */
    public function testUpdateTranslationUnit()
    {
        $response = $this->client->put('translation-units/1', [
            'json' => [
                'source_text' => 'Hello, universe!',
                'status' => 'in-progress',
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Translation unit updated successfully.', $data['message']);
    }

    /**
     * Test deleting a translation unit via the DELETE API endpoint.
     *
     * This test sends a DELETE request to the API to remove a translation unit, and verifies
     * that the response contains a success message indicating that the unit was deleted.
     */
    public function testDeleteTranslationUnit()
    {
        $response = $this->client->delete('translation-units/1');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Translation unit deleted successfully.', $data['message']);
    }
}

