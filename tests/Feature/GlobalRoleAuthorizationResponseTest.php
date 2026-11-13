<?php

use App\Filament\Admin\Resources\RoleResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Gate as AuthorizationGate;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

dataset('global role authorization actions', [
    ['getViewAnyAuthorizationResponse', false],
    ['getCreateAuthorizationResponse', false],
    ['getViewAuthorizationResponse', true],
    ['getEditAuthorizationResponse', true],
    ['getDeleteAuthorizationResponse', true],
]);

function createGlobalRoleAuthorizationAdmin(): User
{
    $user = User::factory()->withPersonalTeam()->create();
    setPermissionsTeamId($user->currentTeam->id);
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web', 'team_id' => $user->currentTeam->id]);
    $user->assignRole($role);

    return $user;
}

function globalRoleAuthorizationTarget(): Role
{
    return new Role()->forceFill(['name' => 'reporting-operator', 'guard_name' => 'web', 'team_id' => null]);
}

it('enforces global-role resource restrictions despite a generic policy grant', function (string $response, bool $record) {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Gate::before(fn () => true);
    $arguments = $record ? [globalRoleAuthorizationTarget()] : [];

    expect(fn () => RoleResource::{$response}(...$arguments)->authorize())->toThrow(AuthorizationException::class);
})->with('global role authorization actions');

it('allows privileged global-role actions only when parent policies also permit them', function (string $response, bool $record) {
    $user = createGlobalRoleAuthorizationAdmin();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Gate::before(fn () => true);
    $arguments = $record ? [globalRoleAuthorizationTarget()] : [];

    expect(RoleResource::{$response}(...$arguments)->allowed())->toBeTrue();
    $gate = new AuthorizationGate($this->app, fn () => $user);
    $gate->before(fn () => false);
    Gate::swap($gate);

    expect(RoleResource::{$response}(...$arguments)->denied())->toBeTrue();
})->with('global role authorization actions');

it('prevents global-role actions on team-owned roles and protects bulk deletion', function () {
    $this->actingAs(createGlobalRoleAuthorizationAdmin());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Gate::before(fn () => true);
    $role = globalRoleAuthorizationTarget()->forceFill(['team_id' => 123]);

    expect(RoleResource::getViewAuthorizationResponse($role)->denied())->toBeTrue()
        ->and(RoleResource::getEditAuthorizationResponse($role)->denied())->toBeTrue()
        ->and(RoleResource::getDeleteAuthorizationResponse($role)->denied())->toBeTrue()
        ->and(RoleResource::getDeleteAnyAuthorizationResponse()->denied())->toBeTrue();
});
