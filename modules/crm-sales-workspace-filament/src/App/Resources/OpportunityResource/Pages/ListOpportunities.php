<?php

declare(strict_types=1);

namespace Liberu\CRM\SalesWorkspace\Filament\App\Resources\OpportunityResource\Pages;

use Liberu\CRM\SalesWorkspace\Filament\App\Resources\OpportunityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
