<?php

declare(strict_types=1);

namespace Liberu\CRM\CoreFilament\App\Resources\SamlConnectionResource\Pages;

use Liberu\CRM\CoreFilament\App\Resources\SamlConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSamlConnection extends CreateRecord
{
    protected static string $resource = SamlConnectionResource::class;
}
