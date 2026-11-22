<?php

declare(strict_types=1);

namespace Liberu\Foundation\Integrations\Services;

use Liberu\CRM\Activities\Models\Task;

interface CalendarService
{
    public function createEvent(Task $task);

    public function updateEvent(Task $task);

    public function deleteEvent(Task $task);

    public function fetchEvents(array $params = []);

    public function syncEvents(array $events);
}
