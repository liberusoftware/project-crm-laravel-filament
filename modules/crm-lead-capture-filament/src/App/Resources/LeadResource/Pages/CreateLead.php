<?php

declare(strict_types=1);

namespace Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource\Pages;

use Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}
