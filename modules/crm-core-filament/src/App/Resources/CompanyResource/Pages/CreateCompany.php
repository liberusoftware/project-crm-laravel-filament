<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\CompanyResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
}
