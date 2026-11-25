<?php

declare(strict_types=1);

namespace Liberu\CRM\CrmSearch\Services;

use Liberu\Foundation\Organizations\Models\Team;

final class CrmSearchPolicy
{
    public function canSearch(int $teamId, int $userId): bool
    {
        $team = Team::query()->find($teamId);

        return $team !== null && ($team->users()->whereKey($userId)->exists() || (int) $team->user_id === $userId);
    }
}
