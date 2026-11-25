<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\LandingPageResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\LandingPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLandingPage extends CreateRecord
{
    protected static string $resource = LandingPageResource::class;
}
