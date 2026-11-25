<?php

declare(strict_types=1);

namespace Liberu\CRM\AutomationPackFilament\App\Resources\WorkflowResource\Pages;

use Liberu\CRM\AutomationPackFilament\App\Resources\WorkflowResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkflow extends EditRecord
{
    protected static string $resource = WorkflowResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
