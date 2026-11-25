<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\ContactResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\ContactResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
