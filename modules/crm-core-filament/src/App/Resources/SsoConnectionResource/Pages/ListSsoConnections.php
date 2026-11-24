<?php

declare(strict_types=1);

namespace Liberu\CRM\CoreFilament\App\Resources\SsoConnectionResource\Pages;

use Liberu\CRM\CoreFilament\App\Resources\SsoConnectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSsoConnections extends ListRecords
{
    protected static string $resource = SsoConnectionResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
