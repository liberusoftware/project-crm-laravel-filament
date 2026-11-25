<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDashboardWidget extends CreateRecord
{
    protected static string $resource = DashboardWidgetResource::class;
}
