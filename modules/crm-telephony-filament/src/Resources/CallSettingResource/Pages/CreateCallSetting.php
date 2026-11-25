<?php

declare(strict_types=1);

namespace Liberu\CRM\Telephony\Filament\Resources\CallSettingResource\Pages;

use Liberu\CRM\Telephony\Filament\Resources\CallSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCallSetting extends CreateRecord
{
    protected static string $resource = CallSettingResource::class;
}
