<?php

declare(strict_types=1);

namespace Liberu\CRM\CustomerSelfServiceFilament\App\Resources\PortalBrandingResource\Pages;

use Liberu\CRM\CustomerSelfServiceFilament\App\Resources\PortalBrandingResource;
use Filament\Resources\Pages\ListRecords;

class ListPortalBranding extends ListRecords
{
    protected static string $resource = PortalBrandingResource::class;
}
