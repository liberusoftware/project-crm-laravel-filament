<?php

declare(strict_types=1);

namespace Liberu\CRM\ServiceAgent\Filament\App\Resources\TicketResource\Pages;

use Liberu\CRM\ServiceAgent\Filament\App\Resources\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
}
