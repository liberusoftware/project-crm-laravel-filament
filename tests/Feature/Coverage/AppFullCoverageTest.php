<?php

declare(strict_types=1);

use App\Filament\CrmNavigation;
use App\Http\Responses\PanelLoginResponse;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\PortalPanelProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\SocialstreamServiceProvider;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Liberu\CRM\MarketingResources\Services\PlatformConfiguration;
use Liberu\CRM\MarketingResources\Services\WorkspaceAccess;
use Liberu\Foundation\ModuleManagerFilament\Pages\ModuleManagement;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Foundation\RolesPermissions\Enums\Role;
use Spatie\Permission\Models\Role as SpatieRole;

it('covers AppServiceProvider factory fallback and module management gate', function () {
    $fallback = Factory::resolveFactoryName('App\\Models\\NonExistentModelForFactoryTest');
    expect($fallback)->toBe('Database\\Factories\\NonExistentModelForFactoryTestFactory');

    $user = User::factory()->create();
    expect(Gate::forUser($user)->allows(ModuleManagement::ABILITY))->toBeFalse();

    config(['app.admin_emails' => [$user->email]]);
    expect(Gate::forUser($user)->allows(ModuleManagement::ABILITY))->toBeTrue();
});

it('covers AuthServiceProvider super admin bypass and non-super-admin fallthrough', function () {
    $provider = new AuthServiceProvider($this->app);
    $provider->boot();

    $regular = User::factory()->create();
    expect(Gate::forUser($regular)->allows('nonexistent-ability-check'))->toBeFalse();

    $superAdmin = User::factory()->create();
    $role = SpatieRole::query()->firstOrCreate(['name' => Role::SuperAdmin->value, 'guard_name' => 'web']);
    $superAdmin->assignRole($role);
    expect(Gate::forUser($superAdmin)->allows('nonexistent-ability-check'))->toBeTrue();
});

it('covers EventServiceProvider, HorizonServiceProvider, JetstreamServiceProvider, and RouteServiceProvider', function () {
    $eventProvider = new EventServiceProvider($this->app);
    expect($eventProvider->shouldDiscoverEvents())->toBeFalse();

    $horizonProvider = new HorizonServiceProvider($this->app);
    $horizonProvider->boot();
    $user = User::factory()->create();
    expect(Gate::forUser($user)->allows('viewHorizon'))->toBeFalse();
    expect(Gate::forUser(null)->allows('viewHorizon'))->toBeFalse();

    $jetstreamProvider = new JetstreamServiceProvider($this->app);
    $jetstreamProvider->register();
    $jetstreamProvider->boot();

    $routeProvider = new RouteServiceProvider($this->app);
    $routeProvider->boot();
    $apiLimiter = RateLimiter::limiter('api');
    $guestReq = Request::create('/api/test', 'GET');
    expect($apiLimiter($guestReq)->maxAttempts)->toBe(60);
    $authReq = Request::create('/api/test', 'GET');
    $authReq->setUserResolver(fn () => $user);
    expect($apiLimiter($authReq)->key)->toBe($user->id);
});

it('covers SocialstreamServiceProvider database exception fallback', function () {
    Schema::dropIfExists('oauth_configurations');
    $provider = new SocialstreamServiceProvider($this->app);
    $provider->register();
    $provider->boot();
    expect(true)->toBeTrue();
});

it('covers AdminPanelProvider, AppPanelProvider, and PortalPanelProvider closures', function () {
    $adminProvider = new AdminPanelProvider($this->app);
    $adminProvider->boot();
    $adminPanel = Filament::getPanel('admin');
    Filament::setTenant(null);
    expect($adminPanel->hasTenantMenu())->toBeFalse()
        ->and($adminPanel->hasNavigation())->toBeFalse();

    $user = User::factory()->withPersonalTeam()->create(['email_verified_at' => now()]);
    Filament::setTenant($user->currentTeam);
    expect($adminPanel->hasTenantMenu())->toBeTrue()
        ->and($adminPanel->hasNavigation())->toBeTrue();

    // AppPanelProvider fallback URLs when no tenant is active
    $appPanel = Filament::getPanel('app');
    Filament::setCurrentPanel($appPanel);
    Filament::setTenant(null);
    $this->actingAs($user);
    foreach ($appPanel->getUserMenuItems() as $item) {
        if (in_array($item->getLabel(), ['Profile', 'Setup wizard', 'Team Settings'], true)) {
            expect($item->getUrl())->toContain('/app');
        }
    }

    // PortalPanelProvider branding closures
    $portalPanel = Filament::getPanel('portal');
    expect($portalPanel->getBrandName())->toBeString();
    $portalPanel->getBrandLogo();
    $portalPanel->getFavicon();

    // Also invoke fresh panel() on PortalPanelProvider
    $freshPortal = (new PortalPanelProvider($this->app))->panel(Panel::make());
    expect($freshPortal->getId())->toBe('portal');
});

it('covers PanelLoginResponse redirects for admin setup, app setup, and default fallback', function () {
    $user = User::factory()->withPersonalTeam()->create(['email_verified_at' => now()]);
    $team = $user->currentTeam;
    $team->forceFill(['setup_completed_at' => null])->save();
    $role = SpatieRole::query()->firstOrCreate(['name' => Role::SuperAdmin->value, 'guard_name' => 'web']);
    $user->assignRole($role);

    $this->actingAs($user);
    $request = Request::create('/login', 'POST');
    $response = new PanelLoginResponse();

    // Admin panel redirect to PlatformSetupWizard when setup not completed
    $platformConfig = Mockery::mock(PlatformConfiguration::class);
    $platformConfig->shouldReceive('settings')->andReturn(null);
    $this->app->instance(PlatformConfiguration::class, $platformConfig);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $adminRedirect = $response->toResponse($request);
    expect($adminRedirect->getTargetUrl())->toContain('admin');

    // App panel redirect to SetupWizard when team setup not completed and user can manage
    $workspaceAccess = Mockery::mock(WorkspaceAccess::class);
    $workspaceAccess->shouldReceive('canManage')->andReturn(true);
    $this->app->instance(WorkspaceAccess::class, $workspaceAccess);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    $appRedirect = $response->toResponse($request);
    expect($appRedirect->getTargetUrl())->toContain('app');

    // Fallback to parent::toResponse when setup is completed
    $team->forceFill(['setup_completed_at' => now()])->save();
    $fallbackRedirect = $response->toResponse($request);
    expect($fallbackRedirect)->not->toBeNull();
});

it('covers every branch of CrmNavigation groupFor', function () {
    $nav = new CrmNavigation();
    $ref = new ReflectionMethod($nav, 'groupFor');

    expect($ref->invoke($nav, 'SalesResource'))->toBe('Sales')
        ->and($ref->invoke($nav, 'MarketingResource'))->toBe('Marketing')
        ->and($ref->invoke($nav, 'ActivityResource'))->toBe('Communication')
        ->and($ref->invoke($nav, 'SupportResource'))->toBe('Support')
        ->and($ref->invoke($nav, 'AnalyticsResource'))->toBe('Data & intelligence')
        ->and($ref->invoke($nav, 'AutomationResource'))->toBe('Operations')
        ->and($ref->invoke($nav, 'OtherThing'))->toBe('CRM');
});

it('covers all methods and branches of App\\Models\\User', function () {
    $user = User::factory()->withPersonalTeam()->create([
        'email_verified_at' => now(),
        'profile_photo_path' => 'https://example.com/avatar.png',
    ]);
    $team = $user->currentTeam;

    expect($user->profile_photo_url)->toBe('https://example.com/avatar.png');

    $user->forceFill(['profile_photo_path' => null])->save();
    expect($user->profile_photo_url)->toBeString();

    expect($user->dashboardWidgets()->getRelated())->not->toBeNull()
        ->and($user->inAppNotifications()->getRelated())->not->toBeNull()
        ->and($user->territories()->getRelated())->not->toBeNull()
        ->and($user->latestTeam()->getRelated())->toBeInstanceOf(Team::class)
        ->and($user->getActivitylogOptions()->logAttributes)->toBe(['name', 'email']);

    $appPanel = Filament::getPanel('app');
    expect($user->getTenants($appPanel)->count())->toBeGreaterThanOrEqual(1)
        ->and($user->canAccessTenant($team))->toBeTrue()
        ->and($user->getDefaultTenant($appPanel)?->id)->toBe($team->id);

    $team->forceFill(['archived_at' => now()])->save();
    $user->refresh();
    expect($user->canAccessTenant($team->fresh()))->toBeFalse()
        ->and($user->getDefaultTenant($appPanel))->toBeNull();

    // Panel access for all panel IDs
    expect($user->canAccessPanel(Panel::make()->id('super_admin')))->toBeFalse()
        ->and($user->canAccessPanel(Panel::make()->id('admin')))->toBeFalse()
        ->and($user->canAccessPanel(Panel::make()->id('portal')))->toBeFalse()
        ->and($user->canAccessPanel(Panel::make()->id('app')))->toBeTrue()
        ->and($user->canAccessFilament())->toBeFalse()
        ->and($user->isAdmin())->toBeFalse()
        ->and($user->isSuperAdmin())->toBeFalse()
        ->and($user->hasAdminAccess())->toBeFalse();

    // hasRole with array (miss + hit) and unsaved user hasGlobalRole
    $unsaved = new User(['name' => 'Unsaved', 'email' => 'unsaved@example.com']);
    expect($unsaved->hasRole('admin'))->toBeFalse()
        ->and($unsaved->hasRole(['admin', 'editor']))->toBeFalse();

    $adminRole = SpatieRole::query()->firstOrCreate(['name' => Role::Admin->value, 'guard_name' => 'web']);
    $tables = config('permission.table_names');
    $morphKey = config('permission.column_names.model_morph_key');
    $teamKey = config('permission.column_names.team_foreign_key');
    $roleKey = app(\Spatie\Permission\PermissionRegistrar::class)->pivotRole;
    DB::table($tables['model_has_roles'])->insert([
        $roleKey => $adminRole->id,
        'model_type' => $user->getMorphClass(),
        $morphKey => $user->getKey(),
        $teamKey => null,
    ]);
    expect($user->hasRole(Role::Admin))->toBeTrue()
        ->and($user->hasRole(['nonexistent', Role::Admin->value]))->toBeTrue()
        ->and($user->hasRole(['nonexistent', 'still-missing']))->toBeFalse();
});
