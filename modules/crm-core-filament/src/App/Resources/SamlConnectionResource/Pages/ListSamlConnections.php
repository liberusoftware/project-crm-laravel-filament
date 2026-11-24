<?php

declare(strict_types=1);

namespace Liberu\CRM\CoreFilament\App\Resources\SamlConnectionResource\Pages;

use Liberu\CRM\CoreFilament\App\Resources\SamlConnectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSamlConnections extends ListRecords
{
    protected static string $resource = SamlConnectionResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
