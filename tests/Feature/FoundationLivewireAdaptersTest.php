<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Livewire\Livewire;

trait BootsFoundationLivewireAdapters
{
    public function createApplication(): Application
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $this->traitsUsedByTest = class_uses_recursive(static::class);
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $optional = ['analytics-google', 'analytics-google-livewire', 'analytics-meta', 'analytics-meta-livewire'];
            $app['config']->set('modules.enabled', array_values(array_unique([
                ...$app['config']->get('modules.enabled', []), ...$optional,
            ])));
            $app['config']->set('modules.disabled', array_values(array_diff($app['config']->get('modules.disabled', []), $optional)));
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}

uses(BootsFoundationLivewireAdapters::class);

it('renders the installed module overview through its public Livewire alias', function (string $module, string $heading) {
    Livewire::test($module.'-overview')
        ->assertSeeHtml('aria-labelledby="'.$module.'-heading"')
        ->assertSee($heading)
        ->assertSee('Core actions remain the authoritative mutation boundary.');
})->with([
    'activity-comments-livewire' => ['activity-comments-livewire', 'Activity and Comments'],
    'analytics-core-livewire' => ['analytics-core-livewire', 'Analytics Core'],
    'analytics-google-livewire' => ['analytics-google-livewire', 'Google Analytics'],
    'analytics-meta-livewire' => ['analytics-meta-livewire', 'Meta Server-Side Tracking'],
    'api-access-livewire' => ['api-access-livewire', 'API Access'],
    'application-livewire' => ['application-livewire', 'Application Core'],
    'audit-livewire' => ['audit-livewire', 'Audit'],
    'currency-context-livewire' => ['currency-context-livewire', 'Currency Context'],
    'developer-experience-livewire' => ['developer-experience-livewire', 'Developer Experience'],
    'feature-flags-livewire' => ['feature-flags-livewire', 'Feature Flags'],
    'identity-core-livewire' => ['identity-core-livewire', 'Identity'],
    'files-media-livewire' => ['files-media-livewire', 'Files and Media'],
    'import-export-livewire' => ['import-export-livewire', 'Import and Export'],
    'integrations-livewire' => ['integrations-livewire', 'Integrations'],
    'jetstream-bridge-livewire' => ['jetstream-bridge-livewire', 'Jetstream Bridge'],
    'module-manager-livewire' => ['module-manager-livewire', 'Module Manager'],
    'notifications-livewire' => ['notifications-livewire', 'Notifications'],
    'observability-livewire' => ['observability-livewire', 'Observability'],
    'organizations-teams-livewire' => ['organizations-teams-livewire', 'Organizations and Teams'],
    'profiles-livewire' => ['profiles-livewire', 'Profiles'],
    'roles-permissions-livewire' => ['roles-permissions-livewire', 'Roles and Permissions'],
    'scheduler-queues-livewire' => ['scheduler-queues-livewire', 'Scheduler and Queues'],
    'search-livewire' => ['search-livewire', 'Search'],
    'sessions-devices-livewire' => ['sessions-devices-livewire', 'Sessions and Devices'],
    'settings-livewire' => ['settings-livewire', 'Settings'],
    'two-factor-authentication-livewire' => ['two-factor-authentication-livewire', 'Two-Factor Authentication'],
    'webhooks-livewire' => ['webhooks-livewire', 'Webhooks'],
]);
