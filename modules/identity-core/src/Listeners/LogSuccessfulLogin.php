<?php

namespace Liberu\Foundation\Identity\Listeners;

use Liberu\Foundation\Audit\Services\AuditLogService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(protected AuditLogService $auditLogService) {}

    public function handle(Login $event): void
    {
        $this->auditLogService->log('login', 'User logged in');
    }
}
