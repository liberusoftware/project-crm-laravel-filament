<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\MarketingCampaignResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\MarketingCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketingCampaign extends CreateRecord
{
    protected static string $resource = MarketingCampaignResource::class;
}
