<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Str;

trait BootsFoundationApiAdapters
{
    public function createApplication(): Application
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $this->traitsUsedByTest = class_uses_recursive(static::class);
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $optional = ['analytics-google', 'analytics-google-api', 'analytics-meta', 'analytics-meta-api'];
            $app['config']->set('modules.enabled', array_values(array_unique([
                ...$app['config']->get('modules.enabled', []), ...$optional,
            ])));
            $app['config']->set('modules.disabled', array_values(array_diff($app['config']->get('modules.disabled', []), $optional)));
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}

uses(BootsFoundationApiAdapters::class);

it('serves the installed versioned API status contract and preserves request identity', function (string $module) {
    $type = substr($module, 0, -4);
    $requestId = '188c7e79-bd4e-496a-9ee3-e25e88a41dfa';
    $this->getJson(route($module.'.status', [], false), ['X-Request-ID' => $requestId])
        ->assertOk()->assertExactJson([
            'data' => ['type' => $type, 'status' => 'available', 'version' => 'v1'],
            'meta' => ['request_id' => $requestId],
        ]);
    $this->getJson('/api/v1/'.$type.'/status')->assertOk()
        ->assertJsonPath('meta.request_id', fn (string $value): bool => Str::isUuid($value));
})->with([
    'activity-comments-api',
    'analytics-core-api',
    'analytics-google-api',
    'analytics-meta-api',
    'api-access-api',
    'application-api',
    'audit-api',
    'currency-context-api',
    'developer-experience-api',
    'feature-flags-api',
    'files-media-api',
    'import-export-api',
    'integrations-api',
    'jetstream-bridge-api',
    'localization-core-api',
    'module-manager-api',
    'notifications-api',
    'observability-api',
    'organizations-teams-api',
    'profiles-api',
    'roles-permissions-api',
    'scheduler-queues-api',
    'sessions-devices-api',
    'settings-api',
    'two-factor-authentication-api',
]);
