<?php

declare(strict_types=1);

namespace Liberu\Foundation\Integrations\Filament\App\Resources\SsoConnectionResource\Pages;

use Liberu\Foundation\Integrations\Filament\App\Resources\SsoConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSsoConnection extends CreateRecord
{
    protected static string $resource = SsoConnectionResource::class;
}
