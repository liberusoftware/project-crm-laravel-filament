<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_reports_application_and_database_status(): void
    {
        $this->get('/health')->assertOk()->assertExactJson([
            'status' => 'ok',
            'checks' => ['application' => 'ok', 'database' => 'ok'],
        ]);
    }

    public function test_health_endpoint_returns_unavailable_without_database_error_details(): void
    {
        DB::shouldReceive('connection')->once()->andThrow(new RuntimeException('private database credentials'));

        $this->get('/health')->assertStatus(503)->assertExactJson([
            'status' => 'degraded',
            'checks' => ['application' => 'ok', 'database' => 'unavailable'],
        ]);
    }
}
