<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\CompanyResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
