<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAd extends CreateRecord
{
    protected static string $resource = AdResource::class;
}
