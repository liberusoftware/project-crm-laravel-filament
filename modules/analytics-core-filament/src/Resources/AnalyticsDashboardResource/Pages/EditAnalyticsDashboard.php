<?php

declare(strict_types=1);

namespace Liberu\Foundation\AnalyticsCoreFilament\Resources\AnalyticsDashboardResource\Pages;

use Liberu\Foundation\AnalyticsCoreFilament\App\Resources\AnalyticsDashboardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnalyticsDashboard extends EditRecord
{
    protected static string $resource = AnalyticsDashboardResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
