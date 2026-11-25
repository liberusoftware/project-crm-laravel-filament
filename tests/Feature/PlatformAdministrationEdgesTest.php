<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Liberu\CRM\ClientOnboardingFilament\Pages\PlatformSetupWizard as SetupWizard;
use Liberu\Foundation\RolesPermissionsFilament\Resources\RoleResource;
use Liberu\Foundation\RolesPermissionsFilament\Resources\RoleResource\Pages\CreateRole;
use Liberu\Foundation\RolesPermissionsFilament\Resources\RoleResource\Pages\EditRole;
use Liberu\Foundation\RolesPermissionsFilament\Resources\RoleResource\Pages\ListRoles;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::setTenant($this->user->currentTeam);
    setPermissionsTeamId(null);
    $this->user->assignRole('super_admin');
    $this->user->unsetRelation('roles')->unsetRelation('permissions');
    config(['services.zernio.mode' => 'direct']);
});

it('creates and deletes a custom global role while bulk deletion remains unavailable', function () {
    Livewire::test(CreateRole::class)->fillForm(['name' => 'Editorial tools', 'permissions' => ['view_contact']])
        ->call('create')->assertHasNoFormErrors();
    $role = Role::query()->whereNull('team_id')->where('name', 'Editorial tools')->firstOrFail();
    expect($role->getPermissionNames()->all())->toBe(['view_contact'])->and(RoleResource::canDeleteAny())->toBeFalse();
    Livewire::test(ListRoles::class)->callTableAction(DeleteAction::class, $role)->assertHasNoTableActionErrors();
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    $this->assertDatabaseHas('audit_logs', ['action' => 'platform.role_deleted', 'auditable_id' => $role->id]);
});

it('shows a field error when creating a reserved system role', function () {
    Livewire::test(CreateRole::class)->fillForm(['name' => 'super_admin', 'permissions' => ['view_contact']])
        ->call('create')->assertHasFormErrors(['name']);
    expect(Role::query()->whereNull('team_id')->where('name', 'super_admin')->count())->toBe(1);
});

it('maps service validation back onto the global role edit form', function () {
    $role = Role::create(['name' => 'Editorial tools', 'guard_name' => 'web', 'team_id' => null]);
    Livewire::test(EditRole::class, ['record' => $role->id])->fillForm(['name' => 'admin', 'permissions' => ['view_contact']])
        ->call('save')->assertHasFormErrors(['name']);
    expect($role->fresh()->name)->toBe('Editorial tools');
});

it('saves platform setup and clears credential inputs after success', function () {
    Livewire::test(SetupWizard::class)->fillForm(['provider' => 'direct', 'ai_enabled' => false])
        ->call('save')->assertHasNoFormErrors()->assertSet('saved', true)
        ->assertNotified('Platform configuration saved')->assertSet('data.facebook_client_secret', null);
    $this->assertDatabaseHas('marketing_platform_settings', ['id' => 1, 'provider' => 'direct']);
});

it('preserves platform setup input when persistence fails', function () {
    DB::statement("CREATE TRIGGER reject_marketing_setup BEFORE INSERT ON marketing_platform_settings BEGIN SELECT RAISE(ABORT, 'temporary storage failure'); END");
    Livewire::test(SetupWizard::class)->fillForm(['provider' => 'direct', 'ai_enabled' => false])
        ->call('save')->assertSet('saved', false)->assertSet('data.provider', 'direct')->assertNotified('Setup could not be saved');
    $this->assertDatabaseCount('marketing_platform_settings', 0);
});
