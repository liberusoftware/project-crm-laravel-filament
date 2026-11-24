<?php

declare(strict_types=1);

namespace App\Providers;

use Liberu\CRM\Core\Events\ContactUpdated;
use Liberu\Foundation\Organizations\Listeners\AssignDefaultTeamRole;
use Liberu\CRM\Core\Listeners\BuildSamlLogoutRedirect;
use Liberu\CRM\Core\Listeners\BuildSsoLogoutRedirect;
use Liberu\CRM\Core\Listeners\LogFailedLogin;
use Liberu\CRM\Core\Listeners\LogPasswordReset;
use Liberu\CRM\Core\Listeners\LogSsoLogout;
use Liberu\CRM\Core\Listeners\LogSuccessfulLogin;
use Liberu\Foundation\Organizations\Listeners\NotifyTeamMembers;
use Liberu\CRM\ChannelGateway\Listeners\ProvisionZernioProfile;
use Liberu\Foundation\Notifications\Listeners\SendCRMEventNotification;
use Liberu\CRM\Activities\Models\Task;
use Liberu\CRM\Activities\Observers\TaskObserver;
use Liberu\CRM\Core\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamMemberAdded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ContactUpdated::class => [
            NotifyTeamMembers::class,
        ],
        Login::class => [
            LogSuccessfulLogin::class,
        ],
        Failed::class => [
            LogFailedLogin::class,
        ],
        PasswordReset::class => [
            LogPasswordReset::class,
        ],
        TeamMemberAdded::class => [
            AssignDefaultTeamRole::class,
        ],
        TeamCreated::class => [
            ProvisionZernioProfile::class,
        ],
        Logout::class => [
            LogSsoLogout::class,
            BuildSsoLogoutRedirect::class,
            BuildSamlLogoutRedirect::class,
        ],
        // Add CRM event listeners
        'Liberu\CRM\Core\Events\NewLead' => [
            SendCRMEventNotification::class,
        ],
        'Liberu\CRM\Core\Events\DealClosed' => [
            SendCRMEventNotification::class,
        ],
        'Liberu\CRM\Core\Events\TaskOverdue' => [
            SendCRMEventNotification::class,
        ],
        'Liberu\CRM\Core\Events\NewComment' => [
            SendCRMEventNotification::class,
        ],
        'Liberu\CRM\Core\Events\MeetingScheduled' => [
            SendCRMEventNotification::class,
        ],
        'Liberu\CRM\ServiceAgent\Events\NewTicket' => [
            SendCRMEventNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    #[\Override]
    public function boot(): void
    {
        Task::observe(TaskObserver::class);

        Event::listen(Logout::class, function ($event): void {
            app(AuditLogService::class)->log('logout', 'User logged out');
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    #[\Override]
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
