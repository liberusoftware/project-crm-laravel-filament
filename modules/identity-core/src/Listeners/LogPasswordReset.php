<?php

declare(strict_types=1);

namespace Liberu\Foundation\Identity\Listeners;

use Liberu\Foundation\Audit\Models\AuditLog;
use Illuminate\Auth\Events\PasswordReset;

class LogPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'action' => 'auth.password_reset',
            'description' => 'Password reset',
            'ip_address' => request()->ip() ?? '0.0.0.0',
        ]);
    }
}
