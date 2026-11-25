<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Liberu\CRM\Activities\Models\Task;
use Liberu\CRM\Activities\Services\TaskAssignmentService;
use Liberu\Foundation\Notifications\Notifications\TaskAssignedNotification;
use Tests\TestCase;

class TaskAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_a_new_assignee(): void
    {
        Notification::fake();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        app(TaskAssignmentService::class)->notify($task);

        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_does_not_notify_when_the_assignee_is_unchanged(): void
    {
        Notification::fake();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        app(TaskAssignmentService::class)->notify($task, $assignee->id);

        Notification::assertNothingSent();
    }
}
