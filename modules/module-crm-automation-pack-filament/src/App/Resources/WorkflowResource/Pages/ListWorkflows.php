<?php

declare(strict_types=1);

namespace Liberu\CRM\AutomationPackFilament\App\Resources\WorkflowResource\Pages;

use Liberu\CRM\AutomationPackFilament\App\Resources\WorkflowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkflows extends ListRecords
{
    protected static string $resource = WorkflowResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
