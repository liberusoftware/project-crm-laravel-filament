<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\Resources\ActivationResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\ActivationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActivation extends EditRecord
{
    protected static string $resource = ActivationResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
