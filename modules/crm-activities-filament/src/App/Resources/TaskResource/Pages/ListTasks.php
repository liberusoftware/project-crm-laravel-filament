<?php

declare(strict_types=1);

namespace Liberu\CRM\Activities\Filament\App\Resources\TaskResource\Pages;

use Liberu\CRM\Activities\Filament\App\Resources\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
