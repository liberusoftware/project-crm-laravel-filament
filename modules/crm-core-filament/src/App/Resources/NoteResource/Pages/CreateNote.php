<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources\NoteResource\Pages;

use Liberu\CRM\Core\Filament\App\Resources\NoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;
}
