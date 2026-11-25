<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;
use Liberu\Foundation\JetstreamBridge\Actions\Jetstream\DeleteUser as DeleteUserWithTeams;
use Liberu\Foundation\Organizations\Actions\Jetstream\AddTeamMember;
use Liberu\Foundation\Organizations\Actions\Jetstream\CreateTeam;
use Liberu\Foundation\Organizations\Actions\Jetstream\DeleteTeam;
use Liberu\Foundation\Organizations\Actions\Jetstream\InviteTeamMember;
use Liberu\Foundation\Organizations\Actions\Jetstream\RemoveTeamMember;
use Liberu\Foundation\Organizations\Actions\Jetstream\UpdateTeamName;
use Liberu\Foundation\Organizations\Listeners\CreatePersonalTeam;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUserWithTeams::class);

        // Use our modified CreatePersonalTeam listener
        Event::listen(
            Registered::class,
            CreatePersonalTeam::class
        );
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

        Jetstream::role('editor', 'Editor', [
            'read',
            'create',
            'update',
        ])->description('Editor users have the ability to read, create, and update.');
    }
}
