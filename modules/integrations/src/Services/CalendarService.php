<?php

declare(strict_types=1);

namespace Liberu\Foundation\Integrations\Services;

use Illuminate\Database\Eloquent\Model;

interface CalendarService
{
    public function createEvent(Model $task);

    public function updateEvent(Model $task);

    public function deleteEvent(Model $task);

    public function fetchEvents(array $params = []);

    public function syncEvents(array $events);
}
