<?php

declare(strict_types=1);

namespace Liberu\CRM\MobileMessagingFilament\Resources\WhatsAppNumberResource\Pages;

use Liberu\CRM\MobileMessagingFilament\Resources\WhatsAppNumberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsAppNumber extends CreateRecord
{
    protected static string $resource = WhatsAppNumberResource::class;
}
