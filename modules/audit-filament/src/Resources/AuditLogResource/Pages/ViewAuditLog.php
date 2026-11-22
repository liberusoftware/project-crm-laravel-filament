<?php

declare(strict_types=1);

namespace Liberu\Foundation\AuditFilament\Resources\AuditLogResource\Pages;

use Liberu\Foundation\AuditFilament\Admin\Resources\AuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;
}
