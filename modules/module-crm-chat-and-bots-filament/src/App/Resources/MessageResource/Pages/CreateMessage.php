<?php

declare(strict_types=1);

namespace Liberu\CRM\ChatAndBotsFilament\App\Resources\MessageResource\Pages;

use Liberu\CRM\ChatAndBotsFilament\App\Resources\MessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;
}
