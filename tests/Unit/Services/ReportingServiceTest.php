<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\CRM\Activities\Models\CrmActivity as Activity;
use Liberu\CRM\Core\Models\CrmContact as Contact;
use Liberu\CRM\SalesWorkspace\Models\Deal;
use Liberu\CRM\SalesWorkspace\Services\ReportingService;
use Tests\TestCase;

class ReportingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $reportingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportingService = new ReportingService();
    }

    public function test_get_contact_interactions_data(): void
    {
        Contact::factory()->count(5)->create();
        Activity::factory()->count(20)->create();

        $result = $this->reportingService->getContactInteractionsData();

        $this->assertCount(5, $result);
        $this->assertArrayHasKey('activities_count', $result->first());
    }

    public function test_get_sales_pipeline_data(): void
    {
        Deal::factory()->count(10)->create();

        $result = $this->reportingService->getSalesPipelineData();

        $this->assertGreaterThan(0, $result->count());
        $this->assertArrayHasKey('stage', $result->first());
        $this->assertArrayHasKey('count', $result->first());

        $this->assertArrayHasKey('total_value', $result->first());
    }

    public function test_get_customer_engagement_data(): void
    {
        Activity::factory()->count(30)->create();

        $result = $this->reportingService->getCustomerEngagementData();

        $this->assertGreaterThan(0, $result->count());
        $this->assertArrayHasKey('date', $result->first());
        $this->assertArrayHasKey('count', $result->first());
    }
}
