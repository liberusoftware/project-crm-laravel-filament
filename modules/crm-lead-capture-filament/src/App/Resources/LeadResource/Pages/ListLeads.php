<?php

declare(strict_types=1);

namespace Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource\Pages;

use Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
