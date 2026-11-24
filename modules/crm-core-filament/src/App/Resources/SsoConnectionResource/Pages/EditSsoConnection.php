<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\SsoConnectionResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\SsoConnectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSsoConnection extends EditRecord
{
    protected static string $resource = SsoConnectionResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
