<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdSetResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdSetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdSet extends EditRecord
{
    protected static string $resource = AdSetResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
