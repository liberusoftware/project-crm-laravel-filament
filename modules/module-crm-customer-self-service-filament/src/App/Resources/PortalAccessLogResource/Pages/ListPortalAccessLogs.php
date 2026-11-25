<?php

declare(strict_types=1);

namespace Liberu\CRM\CustomerSelfServiceFilament\App\Resources\PortalAccessLogResource\Pages;

use Liberu\CRM\CustomerSelfServiceFilament\App\Resources\PortalAccessLogResource;
use Filament\Resources\Pages\ListRecords;

class ListPortalAccessLogs extends ListRecords
{
    protected static string $resource = PortalAccessLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
