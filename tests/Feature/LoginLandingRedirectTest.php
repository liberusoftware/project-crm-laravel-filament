<?php

use Liberu\CRM\ClientOnboardingFilament\Pages\PlatformSetupWizard as AdminSetupWizard;
use App\Filament\App\Pages\SetupWizard as AppSetupWizard;
use App\Http\Responses\PanelLoginResponse;
use App\Http\Responses\RegisterResponse;
use Liberu\CRM\MarketingResources\Models\MarketingPlatformSettings;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Foundation\RolesPermissions\Models\Role;

uses(RefreshDatabase::class);

function seedTeamUser(bool $superAdmin): User
{
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->id])->save();

    if ($superAdmin) {
        setPermissionsTeamId($team->id);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web', 'team_id' => $team->id]);
        $user->assignRole('super_admin');
    }

    return $user;
}

it('sends a super_admin to the admin panel after login', function () {
    $admin = seedTeamUser(superAdmin: true);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertRedirectContains('/admin');
});

it('sends a normal user to the app panel after login', function () {
    $user = seedTeamUser(superAdmin: false);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('filament.app.pages.dashboard', ['tenant' => $user->currentTeam]));
});

it('sends a sales representative through setup after registration', function () {
    $user = seedTeamUser(superAdmin: false);
    setPermissionsTeamId($user->current_team_id);
    Role::firstOrCreate(['name' => 'sales_rep', 'guard_name' => 'web', 'team_id' => $user->current_team_id]);
    $user->assignRole('sales_rep');
    $this->actingAs($user);

    $response = (new RegisterResponse())->toResponse(request()->create('/register', 'POST'));

    expect($response->getTargetUrl())->toBe(url('/app/'.$user->current_team_id.'/setup-wizard'));
});

it('redirects panel logins to admin or app setup wizards when incomplete and falls back once complete', function () {
    $admin = seedTeamUser(superAdmin: true);
    setPermissionsTeamId(null);
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web', 'team_id' => null]);
    $admin->assignRole('super_admin');
    $admin->unsetRelation('roles')->unsetRelation('permissions');

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::auth()->login($admin);
    $request = Request::create('/admin/login', 'POST');

    $adminRedirect = (new PanelLoginResponse())->toResponse($request);
    expect($adminRedirect->getTargetUrl())->toBe(AdminSetupWizard::getUrl(['tenant' => $admin->currentTeam], panel: 'admin'));

    MarketingPlatformSettings::query()->create(['id' => 1, 'provider' => 'direct', 'setup_completed_at' => now()]);
    $adminFallback = (new PanelLoginResponse())->toResponse($request);
    expect($adminFallback->getTargetUrl())->not->toBe(AdminSetupWizard::getUrl(['tenant' => $admin->currentTeam], panel: 'admin'));

    $owner = seedTeamUser(superAdmin: false);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::auth()->login($owner);

    $appRedirect = (new PanelLoginResponse())->toResponse(Request::create('/app/login', 'POST'));
    expect($appRedirect->getTargetUrl())->toBe(AppSetupWizard::getUrl(['tenant' => $owner->currentTeam], panel: 'app'));

    $owner->currentTeam->forceFill(['setup_completed_at' => now()])->save();
    $owner->refresh();
    $appFallback = (new PanelLoginResponse())->toResponse(Request::create('/app/login', 'POST'));
    expect($appFallback->getTargetUrl())->not->toBe(AppSetupWizard::getUrl(['tenant' => $owner->currentTeam], panel: 'app'));
});
