<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\SamlConnectionResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\SamlConnectionResource;
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
