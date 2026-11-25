<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMailchimpCampaigns extends ListRecords
{
    protected static string $resource = MailchimpCampaignResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
