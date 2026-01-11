<?php

declare(strict_types=1);

namespace Protoqol\Sequel\Tests\Feature;

use Protoqol\Sequel\Tests\TestCase;

/**
 * Class IndexTest
 */
final class IndexTest extends TestCase
{
    public function test_index_is_error_if_invalid_database_connection(): void
    {
        // force a "known" state that shouldn't exist, so we can insure an error
        config(['database.default' => 'testing--error-connection']);
        config(['database.connections.testing--error-connection.driver' => 'mysql']);

        $response = $this->get(route('.index'));

        $response->assertStatus(503);
        $response->assertViewIs('Sequel::error');
        $response->assertSeeText('Error in Sequel');
        $response->assertSeeText('Could not create a valid database connection.');
    }

    public function test_index_is_denied_when__is_disabled(): void
    {
        config(['.enabled' => false]);

        $response = $this->get(route('.index'));
        $response->assertStatus(404);
    }

    // additional tests should be ran that indicate successful connections

    // I don't recommend testing with app environment is 'production' because that may cause other side effects
    // instead, in the future, create a method that can be mocked that returns whether we're in production or not.
}
