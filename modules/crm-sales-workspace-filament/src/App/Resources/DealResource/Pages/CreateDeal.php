<?php

declare(strict_types=1);

namespace Liberu\CRM\SalesWorkspace\Filament\App\Resources\DealResource\Pages;

use Liberu\CRM\SalesWorkspace\Filament\App\Resources\DealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;
}
