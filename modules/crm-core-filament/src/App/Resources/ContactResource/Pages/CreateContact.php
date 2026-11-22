<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\ContactResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\ContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
