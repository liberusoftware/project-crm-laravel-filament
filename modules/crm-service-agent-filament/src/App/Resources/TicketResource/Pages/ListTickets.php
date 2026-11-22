<?php

declare(strict_types=1);

namespace Liberu\CRM\ServiceAgent\Filament\App\Resources\TicketResource\Pages;

use Liberu\CRM\ServiceAgent\Filament\App\Resources\TicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
