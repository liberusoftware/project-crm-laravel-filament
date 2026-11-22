<?php

declare(strict_types=1);

namespace Liberu\CRM\AutomationPackFilament\App\Resources\WorkflowResource\Pages;

use Liberu\CRM\AutomationPackFilament\App\Resources\WorkflowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflow extends CreateRecord
{
    protected static string $resource = WorkflowResource::class;
}
