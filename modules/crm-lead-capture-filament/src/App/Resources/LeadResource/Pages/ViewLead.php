<?php

declare(strict_types=1);

namespace Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource\Pages;

use Liberu\CRM\DialerAndOutreachFilament\Actions\ContactDialingActions;
use Liberu\CRM\LeadCaptureFilament\App\Resources\LeadResource;
use Liberu\CRM\LeadCapture\Models\Lead;
use Liberu\Foundation\Organizations\Support\AccessContext;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [ContactDialingActions::quick(), EditAction::make()];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('status'),
            TextEntry::make('source'),
            TextEntry::make('lifecycle_stage'),
            TextEntry::make('score')->label('Lead Score'),
            // Same masking as the table column: the `free` role never sees the value.
            TextEntry::make('potential_value')
                ->getStateUsing(fn (Lead $record): string => AccessContext::shouldMaskFields()
                    ? '[hidden]'
                    : '$'.number_format((float) $record->potential_value, 2)),
            TextEntry::make('expected_close_date')->date(),
            TextEntry::make('contact.name')->label('Contact'),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }
}
