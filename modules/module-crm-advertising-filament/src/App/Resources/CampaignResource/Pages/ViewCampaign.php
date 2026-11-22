<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\App\Resources\CampaignResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\CampaignResource;
use Liberu\CRM\Advertising\Models\Campaign;
use Liberu\Foundation\Organizations\Support\AccessContext;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('status'),
            TextEntry::make('objective'),
            // Masked for masked-role (`free`) viewers, same gate as the table
            // column, so the detail view is not a masking bypass.
            TextEntry::make('budget')
                ->getStateUsing(fn (Campaign $record): string => AccessContext::shouldMaskFields()
                    ? '[hidden]'
                    : '$'.number_format((float) $record->budget, 2)),
            TextEntry::make('budget_type'),
            TextEntry::make('start_date')->dateTime(),
            TextEntry::make('end_date')->dateTime(),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }
}
