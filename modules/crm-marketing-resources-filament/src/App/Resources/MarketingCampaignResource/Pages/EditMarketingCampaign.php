<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\MarketingCampaignResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\MarketingCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingCampaign extends EditRecord
{
    protected static string $resource = MarketingCampaignResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
