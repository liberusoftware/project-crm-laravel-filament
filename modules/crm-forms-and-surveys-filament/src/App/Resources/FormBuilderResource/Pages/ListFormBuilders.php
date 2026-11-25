<?php

declare(strict_types=1);

namespace Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource\Pages;

use Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormBuilders extends ListRecords
{
    protected static string $resource = FormBuilderResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
