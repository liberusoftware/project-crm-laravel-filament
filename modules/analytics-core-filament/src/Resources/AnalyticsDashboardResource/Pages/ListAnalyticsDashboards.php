<?php

declare(strict_types=1);

namespace Liberu\Foundation\AnalyticsCoreFilament\Resources\AnalyticsDashboardResource\Pages;

use Liberu\Foundation\AnalyticsCoreFilament\App\Resources\AnalyticsDashboardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnalyticsDashboards extends ListRecords
{
    protected static string $resource = AnalyticsDashboardResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
