<?php

namespace Liberu\CRM\Core\Listeners;

use Liberu\CRM\Core\Services\AuditLogService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(protected AuditLogService $auditLogService) {}

    public function handle(Login $event): void
    {
        $this->auditLogService->log('login', 'User logged in');
    }
}
