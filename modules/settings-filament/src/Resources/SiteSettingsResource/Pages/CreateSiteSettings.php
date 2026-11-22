<?php

declare(strict_types=1);

namespace Liberu\Foundation\SettingsFilament\Resources\SiteSettingsResource\Pages;

use App\Filament\Admin\Resources\SiteSettingsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteSettings extends CreateRecord
{
    protected static string $resource = SiteSettingsResource::class;
}
