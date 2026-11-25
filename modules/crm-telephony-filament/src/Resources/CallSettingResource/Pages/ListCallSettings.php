<?php

declare(strict_types=1);

namespace Liberu\CRM\Telephony\Filament\Resources\CallSettingResource\Pages;

use Liberu\CRM\Telephony\Filament\Resources\CallSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCallSettings extends ListRecords
{
    protected static string $resource = CallSettingResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
