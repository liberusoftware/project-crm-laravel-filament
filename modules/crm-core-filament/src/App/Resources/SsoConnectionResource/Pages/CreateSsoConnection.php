<?php

declare(strict_types=1);

namespace Liberu\CRM\CoreFilament\App\Resources\SsoConnectionResource\Pages;

use Liberu\CRM\CoreFilament\App\Resources\SsoConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSsoConnection extends CreateRecord
{
    protected static string $resource = SsoConnectionResource::class;
}
