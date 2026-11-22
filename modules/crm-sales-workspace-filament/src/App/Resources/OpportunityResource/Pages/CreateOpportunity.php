<?php

declare(strict_types=1);

namespace Liberu\CRM\SalesWorkspace\Filament\App\Resources\OpportunityResource\Pages;

use Liberu\CRM\SalesWorkspace\Filament\App\Resources\OpportunityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;
}
