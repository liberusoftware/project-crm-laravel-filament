<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\Resources\ActivationResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\ActivationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivation extends CreateRecord
{
    protected static string $resource = ActivationResource::class;
}
