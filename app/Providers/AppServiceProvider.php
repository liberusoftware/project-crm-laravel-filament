<?php

declare(strict_types=1);

namespace App\Providers;

use Liberu\Foundation\Organizations\Console\Commands\BackupTeam;
use Liberu\Foundation\Organizations\Console\Commands\CloneTeam;
use Liberu\CRM\Core\Console\Commands\FreshDatabaseCommand;
use Liberu\Foundation\Organizations\Console\Commands\ImportTeam;
use Liberu\CRM\Activities\Console\Commands\NotifyOverdueTasks;
use Liberu\CRM\MarketingResources\Console\Commands\PublishScheduledPosts;
use Liberu\Foundation\Organizations\Console\Commands\RestoreTeam;
use Liberu\CRM\Activities\Console\Commands\SendReminders;
use Liberu\CRM\MarketingResources\Console\Commands\UpdatePostAnalytics;
use App\Http\Responses\PanelLoginResponse;
use Liberu\CRM\Core\Models\CrmContact as Contact;
use Liberu\CRM\SalesWorkspace\Models\Deal;
use Liberu\CRM\LeadCapture\Models\Lead;
use Liberu\CRM\SalesWorkspace\Models\Opportunity;
use Liberu\CRM\Activities\Models\Task;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\CRM\Core\Observers\AuditObserver;
use Liberu\CRM\Core\Support\SsoLogoutState;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\Console\VendorPublishCommand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;
use Liberu\Foundation\ModuleManager\ModuleDiscovery;
use Liberu\Foundation\ModuleManager\ModuleManagerServiceProvider;
use Liberu\Foundation\ModuleManager\ModuleRegistry;
use Liberu\Foundation\ModuleManagerFilament\Pages\ModuleManagement;
use Liberu\Foundation\Observability\ObservabilityServiceProvider;
use Liberu\Foundation\Search\SearchServiceProvider;
use Liberu\Foundation\Settings\SettingsServiceProvider;
use Liberu\Foundation\Theme\Providers\ThemeServiceProvider;
use Symfony\Component\Console\Input\InputOption;

class AppServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, PanelLoginResponse::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                FreshDatabaseCommand::class,
                InstallCommand::class,
                KeyGenerateCommand::class,
                MigrateCommand::class,
                StatusCommand::class,
                WipeCommand::class,
                PackageDiscoverCommand::class,
                SeedCommand::class,
                VendorPublishCommand::class,
            ]);
            $this->commands([
                BackupTeam::class,
                CloneTeam::class,
                ImportTeam::class,
                NotifyOverdueTasks::class,
                PublishScheduledPosts::class,
                RestoreTeam::class,
                SendReminders::class,
                UpdatePostAnalytics::class,
            ]);
        }

        // Request-scoped holder for the SSO single-logout redirect URL.
        $this->app->singleton(SsoLogoutState::class);
        $this->app->singleton(ModuleRegistry::class, fn (): ModuleRegistry => (new ModuleDiscovery())->discover([base_path('modules')]));
        $this->app->register(ModuleManagerServiceProvider::class);
        $this->app->register(SettingsServiceProvider::class);
        $this->app->register(SearchServiceProvider::class);
        $this->app->register(ObservabilityServiceProvider::class);
        $this->app->register(ThemeServiceProvider::class);
    }

    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            $base = class_basename($modelName);
            $aliases = [
                'CrmContact' => 'ContactFactory',
                'CrmActivity' => 'ActivityFactory',
                'CrmNote' => 'NoteFactory',
                'CrmTag' => 'TagFactory',
                'MailTemplate' => 'EmailTemplateFactory',
            ];
            $candidate = 'Database\\Factories\\'.($aliases[$base] ?? "{$base}Factory");

            if (class_exists($candidate)) {
                return $candidate;
            }

            return 'Database\\Factories\\'.ltrim(str_replace('App\\Models\\', '', $modelName), '\\').'Factory';
        });

        Gate::define(ModuleManagement::ABILITY, fn ($user): bool => method_exists($user, 'isAdmin') && $user->isAdmin());

        Jetstream::useTeamModel(Team::class);

        if ($this->app->runningInConsole()) {
            $this->app->resolving(SeedCommand::class, function (SeedCommand $command): void {
                if (! $command->getDefinition()->hasOption('tenants')) {
                    $command->getDefinition()->addOption(new InputOption('tenants', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL));
                }
            });
        }

        // Audit core tenant models. Never observe AuditLog itself -> infinite recursion.
        foreach ([Contact::class, Deal::class, Lead::class, Opportunity::class, Task::class] as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
