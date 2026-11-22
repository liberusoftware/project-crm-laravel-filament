<?php

declare(strict_types=1);

namespace Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource\Pages;

use Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormBuilder extends CreateRecord
{
    protected static string $resource = FormBuilderResource::class;
}
