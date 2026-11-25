<?php

declare(strict_types=1);

namespace Liberu\CRM\ChatAndBotsFilament\App\Resources\MessageResource\Pages;

use Liberu\CRM\ChatAndBotsFilament\App\Resources\MessageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessage extends EditRecord
{
    protected static string $resource = MessageResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
