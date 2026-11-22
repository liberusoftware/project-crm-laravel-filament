<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\Resources\ActivationResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\ActivationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivations extends ListRecords
{
    protected static string $resource = ActivationResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
