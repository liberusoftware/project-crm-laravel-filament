<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAds extends ListRecords
{
    protected static string $resource = AdResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
