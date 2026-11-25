<?php

declare(strict_types=1);

namespace Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource\Pages;

use Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
