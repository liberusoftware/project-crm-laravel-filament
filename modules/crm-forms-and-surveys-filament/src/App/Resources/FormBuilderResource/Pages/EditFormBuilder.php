<?php

declare(strict_types=1);

namespace Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource\Pages;

use Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFormBuilder extends EditRecord
{
    protected static string $resource = FormBuilderResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
