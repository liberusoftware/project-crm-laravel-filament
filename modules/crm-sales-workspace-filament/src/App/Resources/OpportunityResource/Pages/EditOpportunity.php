<?php

declare(strict_types=1);

namespace Liberu\CRM\SalesWorkspace\Filament\App\Resources\OpportunityResource\Pages;

use Liberu\CRM\SalesWorkspace\Filament\App\Resources\OpportunityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    protected static string $resource = OpportunityResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
