<?php

declare(strict_types=1);

namespace Liberu\CRM\GoalsAndPerformance\Services;

use Liberu\Foundation\Organizations\Models\Team;

final class PerformancePolicy
{
    public function canManage(int $teamId, int $userId): bool
    {
        $team = Team::query()->find($teamId);

        return $team !== null && ($team->user_id === $userId || $team->users()->whereKey($userId)->wherePivotIn('role', ['admin', 'owner', 'manager', 'sales'])->exists());
    }
}
