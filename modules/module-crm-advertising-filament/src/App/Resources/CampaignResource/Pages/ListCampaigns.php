<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\App\Resources\CampaignResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\CampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
