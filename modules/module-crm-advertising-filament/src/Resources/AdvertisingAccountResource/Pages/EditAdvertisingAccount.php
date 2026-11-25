<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdvertisingAccountResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdvertisingAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisingAccount extends EditRecord
{
    protected static string $resource = AdvertisingAccountResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
