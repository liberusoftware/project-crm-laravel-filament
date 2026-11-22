<?php

declare(strict_types=1);

namespace Liberu\CRM\Telephony\Filament\Resources\CallSettingResource\Pages;

use Liberu\CRM\Telephony\Filament\Resources\CallSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCallSetting extends EditRecord
{
    protected static string $resource = CallSettingResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
