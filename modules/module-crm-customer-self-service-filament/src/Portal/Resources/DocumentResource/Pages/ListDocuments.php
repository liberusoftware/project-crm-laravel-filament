<?php

declare(strict_types=1);

namespace Liberu\CRM\CustomerSelfServiceFilament\Portal\Resources\DocumentResource\Pages;

use Liberu\CRM\CustomerSelfServiceFilament\Portal\Resources\DocumentResource;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
