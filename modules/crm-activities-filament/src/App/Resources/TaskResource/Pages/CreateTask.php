<?php

namespace Liberu\CRM\Activities\Filament\App\Resources\TaskResource\Pages;

use Liberu\CRM\Activities\Filament\App\Resources\TaskResource;
use Liberu\CRM\Activities\Models\Task;
use Liberu\CRM\Activities\Services\TaskAssignmentService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    #[\Override]
    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components([
                ...parent::form($schema)->getComponents(),
                DateTimePicker::make('reminder_date')
                    ->label('Reminder Date'),
                Select::make('calendar_type')
                    ->label('Sync to Calendar')
                    ->options([
                        'none' => 'No Sync',
                        'google' => 'Google Calendar',
                        'outlook' => 'Outlook Calendar',
                    ])
                    ->default('none')
                    ->live(),
            ]);
    }

    protected function afterCreate(): void
    {
        if (! $this->record instanceof Task) {
            return;
        }

        app(TaskAssignmentService::class)->notify($this->record);

        if ($this->record->calendar_type !== 'none') {
            $this->record->syncWithCalendar();
        }
    }
}
