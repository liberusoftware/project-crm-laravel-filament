<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDashboardWidgets extends ListRecords
{
    protected static string $resource = DashboardWidgetResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
