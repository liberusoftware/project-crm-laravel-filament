<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdSetResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdSetResource;
use Liberu\CRM\Advertising\Models\AdSet;
use Liberu\Foundation\Organizations\Support\AccessContext;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAdSet extends ViewRecord
{
    protected static string $resource = AdSetResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('advertisingAccount.name')->label('Account'),
            TextEntry::make('campaign.name')->label('Campaign'),
            TextEntry::make('status'),
            // budget is masked for masked-role (`free`) viewers with the SAME gate
            // as the table column, so the detail view is not a masking bypass.
            TextEntry::make('budget')
                ->getStateUsing(fn (AdSet $record): string => AccessContext::shouldMaskFields()
                    ? '[hidden]'
                    : '$'.number_format((float) $record->budget, 2)),
            TextEntry::make('budget_type'),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }
}
