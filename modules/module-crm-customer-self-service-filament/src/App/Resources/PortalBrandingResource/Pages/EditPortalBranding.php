<?php

declare(strict_types=1);

namespace Liberu\CRM\CustomerSelfServiceFilament\App\Resources\PortalBrandingResource\Pages;

use Liberu\CRM\CustomerSelfServiceFilament\App\Resources\PortalBrandingResource;
use Filament\Resources\Pages\EditRecord;

class EditPortalBranding extends EditRecord
{
    protected static string $resource = PortalBrandingResource::class;
}
