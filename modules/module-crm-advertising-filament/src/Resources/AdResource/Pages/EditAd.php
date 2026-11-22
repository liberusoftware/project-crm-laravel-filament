<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAd extends EditRecord
{
    protected static string $resource = AdResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
