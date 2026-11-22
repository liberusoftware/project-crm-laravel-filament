<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdSetResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdSetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdSet extends CreateRecord
{
    protected static string $resource = AdSetResource::class;
}
