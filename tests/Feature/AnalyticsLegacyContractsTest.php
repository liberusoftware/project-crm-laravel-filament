<?php

use Liberu\Foundation\AnalyticsCoreFilament\App\Resources\AnalyticsDashboardResource;
use Liberu\Foundation\AnalyticsCoreFilament\Resources\AnalyticsDashboardResource\Pages\EditAnalyticsDashboard;
use Liberu\Foundation\AnalyticsCoreFilament\Resources\AnalyticsDashboardResource\Pages\ListAnalyticsDashboards;
use Liberu\Foundation\AnalyticsCoreFilament\Resources\AnalyticsDashboardResource\Pages\ViewAnalyticsDashboard;
use Liberu\Foundation\AnalyticsCoreFilament\App\Widgets\SocialLinksWidget;
use Liberu\Foundation\AnalyticsCoreFilament\Widgets\AdPerformanceWidget;
use Liberu\Analytics\Core\Http\Controllers\ReportController;
use Liberu\Foundation\AnalyticsCoreLivewire\Dashboard;
use Liberu\Foundation\AnalyticsCoreLivewire\ReportCustomizer;
use Liberu\Foundation\AnalyticsCoreLivewire\Widgets\Stats;
use Liberu\Foundation\AnalyticsCoreLivewire\Widgets\Tasks;
use Liberu\CRM\Advertising\Models\AdvertisingAccount;
use Liberu\Analytics\Core\Models\DashboardWidget;
use Liberu\Analytics\Core\Models\ReportBuilder;
use App\Models\User;
use Liberu\CRM\EmailProductivity\Services\MailChimpService;
use Liberu\CRM\SalesWorkspace\Services\ReportingService;
use Liberu\Foundation\Settings\Settings\GeneralSettings;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

it('passes report request filters and campaign identities to the owning service', function (string $method, string $serviceMethod, string $template, bool $campaign): void {
    $filters = ['start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'campaign_id' => 'campaign-123'];
    $data = $campaign ? ['campaign_id' => 'campaign-123', 'opens' => 42] : collect([['name' => 'Ada', 'count' => 2]]);
    $reports = Mockery::mock(ReportingService::class);
    $mailchimp = Mockery::mock(MailChimpService::class);
    ($campaign ? $mailchimp : $reports)->shouldReceive($serviceMethod)->once()
        ->with($campaign ? 'campaign-123' : $filters)->andReturn($data);
    $view = Mockery::mock(ViewContract::class);
    View::shouldReceive('make')->once()->with($template, ['data' => $data], [])->andReturn($view);

    expect((new ReportController($reports, $mailchimp))->$method(Request::create('/reports', 'GET', $filters)))->toBe($view);
})->with([
    'contact interactions' => ['generateContactInteractionsReport', 'getContactInteractionsData', 'reports.contact-interactions', false],
    'sales pipeline' => ['generateSalesPipelineReport', 'getSalesPipelineData', 'reports.sales-pipeline', false],
    'customer engagement' => ['generateCustomerEngagementReport', 'getCustomerEngagementData', 'reports.customer-engagement', false],
    'campaign experiment' => ['generateABTestResultsReport', 'getABTestResults', 'reports.ab-test-results', true],
    'email performance' => ['generateEmailCampaignReport', 'getCampaignReport', 'reports.email-campaign-performance', true],
]);

it('builds legacy report chart payloads and passes the same data to its view', function (string $componentClass, string $type, ?string $method, ?string $chart, ?string $label, ?string $value): void {
    $rows = collect([(object) ['name' => 'Ada', 'activities_count' => 3, 'stage' => 'qualified', 'total_value' => 120, 'date' => '2026-01-01', 'count' => 4]]);
    $filters = ['date_range' => 'last-7-days'];
    $service = Mockery::mock(ReportingService::class);
    if ($method !== null) {
        $service->shouldReceive($method)->twice()->with($filters)->andReturn($rows);
    }
    $component = new $componentClass();
    $component->boot($service);
    $component->reportType = $type;
    $component->filters = $filters;
    $data = $component->generateReport();
    if ($method === null) {
        expect($data)->toBe([]);
    } else {
        expect($data['type'])->toBe($chart)->and($data['raw'])->toBe($rows)
            ->and($data['data']['labels']->all())->toBe($rows->pluck($label)->all())
            ->and($data['data']['datasets'][0]['data']->all())->toBe($rows->pluck($value)->all());
    }
    $view = Mockery::mock(ViewContract::class);
    View::shouldReceive('make')->once()->with('livewire.report-customizer', Mockery::on(
        fn (array $viewData): bool => $viewData['data'] == $data,
    ), [])->andReturn($view);
    expect($component->render())->toBe($view);
    $component->updatedReportType();
    expect($component->filters)->toBe([]);
    $component->dateRange = 'last-90-days';
    $component->updatedDateRange();
    expect($component->filters)->toBe(['date_range' => 'last-90-days']);
})->with([
    'previous namespace' => Liberu\Analytics\Core\Http\Livewire\ReportCustomizer::class,
    'current namespace' => ReportCustomizer::class,
])->with([
    'contacts' => ['contact-interactions', 'getContactInteractionsData', 'pie', 'name', 'activities_count'],
    'pipeline' => ['sales-pipeline', 'getSalesPipelineData', 'bar', 'stage', 'total_value'],
    'engagement' => ['customer-engagement', 'getCustomerEngagementData', 'line', 'date', 'count'],
    'unknown report' => ['unsupported', null, null, null, null],
]);

it('persists widget creation ordering and removal through both dashboard namespaces', function (string $componentClass): void {
    $user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($user);
    $first = DashboardWidget::factory()->create(['user_id' => $user->id, 'position' => 2, 'widget_type' => 'stats']);
    $second = DashboardWidget::factory()->create(['user_id' => $user->id, 'position' => 1, 'widget_type' => 'tasks']);
    $component = new $componentClass();
    $component->mount();
    expect($component->widgets->modelKeys())->toBe([$second->id, $first->id]);
    $component->updateWidgetOrder([$first->id, $second->id]);
    expect($first->fresh()->position)->toBe(1)->and($second->fresh()->position)->toBe(2);
    $component->addWidget('stats');
    expect($component->widgets)->toHaveCount(3);
    $created = $component->widgets->last();
    expect($created->user_id)->toBe($user->id)->and($created->position)->toBe(3);
    $component->removeWidget($second->id);
    expect($component->widgets->modelKeys())->toBe([$first->id, $created->id]);
    $this->assertDatabaseMissing('dashboard_widgets', ['id' => $second->id]);
    expect($component->render()->name())->toBe('livewire.dashboard');
})->with([Liberu\Analytics\Core\Http\Livewire\Dashboard::class, Dashboard::class]);

it('renders the shipped stats and task widget views through both namespaces', function (string $componentClass, string $text): void {
    $component = new $componentClass();
    $component->widget = DashboardWidget::factory()->make();

    expect($component->render()->render())->toContain($text);
})->with([
    [Liberu\Analytics\Core\Http\Livewire\Widgets\Stats::class, 'Stats Widget'],
    [Liberu\Analytics\Core\Http\Livewire\Widgets\Tasks::class, 'Tasks Widget'],
    [Stats::class, 'Stats Widget'],
    [Tasks::class, 'Tasks Widget'],
]);

it('resolves the author relationship of a saved report definition', function (): void {
    $author = User::factory()->create();
    $report = new ReportBuilder(['created_by' => $author->id]);

    expect($report->creator)->toBeInstanceOf(User::class)->and($report->creator->id)->toBe($author->id);
});

it('uses configured social links and supplies defaults only when all settings are empty', function (bool $configured): void {
    $settings = Mockery::mock(GeneralSettings::class)->makePartial();
    foreach (['github', 'facebook', 'twitter', 'youtube'] as $network) {
        $settings->{$network.'_url'} = $configured ? 'https://'.$network.'.example.test/profile' : null;
    }
    app()->instance(GeneralSettings::class, $settings);
    $expected = $configured ? [
        'GitHub' => 'https://github.example.test/profile', 'Facebook' => 'https://facebook.example.test/profile',
        'Twitter' => 'https://twitter.example.test/profile', 'YouTube' => 'https://youtube.example.test/profile',
    ] : [
        'GitHub' => 'https://www.github.com/liberu-genealogy',
        'Facebook Page' => 'https://www.facebook.com/familytree365',
        'Facebook Groups' => [
            'Family Tree 365' => 'https://www.facebook.com/groups/familytree365',
            'Genealogy Chat' => 'https://www.facebook.com/groups/genealogychat',
            'DNA 365' => 'https://www.facebook.com/groups/dna365',
        ],
    ];
    $view = Mockery::mock(ViewContract::class);
    View::shouldReceive('make')->once()->with('filament.app.widgets.social-links-widget', ['links' => $expected], [])->andReturn($view);

    expect((new SocialLinksWidget())->render())->toBe($view);
})->with(['configured links' => true, 'fallback links' => false]);

it('aggregates metrics only from active advertising accounts', function (): void {
    if (! class_exists('App\\Services\\HostAnalyticsMetricsService', false)) {
        class_alias(AnalyticsMetricsFixture::class, 'App\\Services\\HostAnalyticsMetricsService');
    }
    AdvertisingAccount::factory()->create(['platform' => 'Host Analytics Metrics', 'status' => true, 'metadata' => ['impressions' => 1200, 'clicks' => 50, 'conversions' => 3]]);
    AdvertisingAccount::factory()->create(['platform' => 'Host Analytics Metrics', 'status' => true, 'metadata' => ['impressions' => 800, 'clicks' => 25, 'conversions' => 2]]);
    AdvertisingAccount::factory()->create(['platform' => 'Host Analytics Metrics', 'status' => false, 'metadata' => ['impressions' => 99999, 'clicks' => 99999, 'conversions' => 99999]]);
    $widget = new class() extends AdPerformanceWidget
    {
        public function cards(): array
        {
            return $this->getCards();
        }
    };
    $cards = $widget->cards();

    expect(array_map(fn ($card) => $card->getLabel(), $cards))->toBe(['Total Impressions', 'Total Clicks', 'Total Conversions'])
        ->and(array_map(fn ($card) => $card->getValue(), $cards))->toBe(['2,000', '75', '5']);
});

it('exposes the intended create and delete dashboard page actions', function (): void {
    $list = new class() extends ListAnalyticsDashboards
    {
        public function actions(): array
        {
            return $this->getHeaderActions();
        }
    };
    $edit = new class() extends EditAnalyticsDashboard
    {
        public function actions(): array
        {
            return $this->getHeaderActions();
        }
    };

    expect($list->actions())->toHaveCount(1)
        ->and($list->actions()[0])->toBeInstanceOf(CreateAction::class)
        ->and($edit->actions())->toHaveCount(1)
        ->and($edit->actions()[0])->toBeInstanceOf(DeleteAction::class);
});

it('exports report rows as CSV and leaves empty reports without a file', function (bool $empty): void {
    $rows = $empty ? collect() : collect([['name' => 'Ada, Lovelace', 'activities_count' => 3]]);
    $service = Mockery::mock(ReportingService::class);
    $service->shouldReceive('getContactInteractionsData')->once()->with(['start_date' => null, 'end_date' => null])->andReturn($rows);
    app()->instance(ReportingService::class, $service);
    $directory = sys_get_temp_dir().'/analytics-export-'.bin2hex(random_bytes(8));
    mkdir($directory.'/app/public/reports', 0700, true);
    $originalStorage = storage_path();
    app()->useStoragePath($directory);
    try {
        $page = new Liberu\Foundation\AnalyticsCoreFilament\Pages\ReportCustomizer();
        $page->exportCsv();
        $path = $directory.'/app/public/reports/contact-interactions_'.now()->format('Y-m-d').'.csv';
        expect(file_exists($path))->toBe(! $empty);
        if (! $empty) {
            $file = fopen($path, 'r');
            try {
                expect(fgetcsv($file, escape: '\\'))->toBe(['name', 'activities_count'])
                    ->and(fgetcsv($file, escape: '\\'))->toBe(['Ada, Lovelace', '3'])
                    ->and(fgetcsv($file, escape: '\\'))->toBeFalse();
            } finally {
                fclose($file);
            }
        }
    } finally {
        app()->useStoragePath($originalStorage);
        (new Filesystem())->deleteDirectory($directory);
    }
})->with(['populated report' => false, 'empty report' => true]);

it('provides the analytics resource route title and empty widget layouts', function (): void {
    $resource = AnalyticsDashboardResource::class;
    $page = new class() extends ViewAnalyticsDashboard
    {
        public function headerWidgetDefinitions(): array
        {
            return $this->getHeaderWidgets();
        }

        public function footerWidgetDefinitions(): array
        {
            return $this->getFooterWidgets();
        }
    };

    expect($resource::getPages())->toHaveKey('index')
        ->and($resource::getWidgets())->toBe([])
        ->and($page->getTitle())->toBe('Analytics Dashboard')
        ->and($page->headerWidgetDefinitions())->toBe([])
        ->and($page->footerWidgetDefinitions())->toBe([])
        ->and($page->getHeaderWidgetsColumns())->toBe(1)
        ->and($page->getFooterWidgetsColumns())->toBe(2);
});

final readonly class AnalyticsMetricsFixture
{
    public function __construct(private AdvertisingAccount $account) {}

    public function getPerformanceMetrics(): array
    {
        return $this->account->metadata;
    }
}
