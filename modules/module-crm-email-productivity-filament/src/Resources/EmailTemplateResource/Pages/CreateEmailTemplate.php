<?php

namespace Liberu\CRM\EmailProductivityFilament\Resources\EmailTemplateResource\Pages;

use Liberu\CRM\EmailProductivityFilament\Resources\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    #[\Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
