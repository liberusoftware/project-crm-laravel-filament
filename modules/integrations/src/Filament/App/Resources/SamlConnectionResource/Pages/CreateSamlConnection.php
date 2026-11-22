<?php

declare(strict_types=1);

namespace Liberu\Foundation\Integrations\Filament\App\Resources\SamlConnectionResource\Pages;

use Liberu\Foundation\Integrations\Filament\App\Resources\SamlConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSamlConnection extends CreateRecord
{
    protected static string $resource = SamlConnectionResource::class;
}
