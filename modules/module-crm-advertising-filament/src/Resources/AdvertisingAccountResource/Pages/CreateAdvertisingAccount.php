<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdvertisingAccountResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdvertisingAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvertisingAccount extends CreateRecord
{
    protected static string $resource = AdvertisingAccountResource::class;
}
