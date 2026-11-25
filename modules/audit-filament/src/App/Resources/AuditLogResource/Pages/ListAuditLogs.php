<?php

declare(strict_types=1);

namespace Liberu\Foundation\AuditFilament\App\Resources\AuditLogResource\Pages;

use Liberu\Foundation\AuditFilament\App\Resources\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;
}
