<?php

declare(strict_types=1);

namespace Liberu\Foundation\AnalyticsCoreFilament\Resources\AnalyticsDashboardResource\Pages;

use Liberu\Foundation\AnalyticsCoreFilament\App\Resources\AnalyticsDashboardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnalyticsDashboard extends CreateRecord
{
    protected static string $resource = AnalyticsDashboardResource::class;
}
