<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMailchimpCampaign extends EditRecord
{
    protected static string $resource = MailchimpCampaignResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
