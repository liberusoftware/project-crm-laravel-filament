<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdSetResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdSets extends ListRecords
{
    protected static string $resource = AdSetResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
