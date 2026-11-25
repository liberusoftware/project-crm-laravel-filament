<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\MailchimpCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMailchimpCampaign extends CreateRecord
{
    protected static string $resource = MailchimpCampaignResource::class;
}
