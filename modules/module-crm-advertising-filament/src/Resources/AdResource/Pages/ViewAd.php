<?php

declare(strict_types=1);

namespace Liberu\CRM\AdvertisingFilament\Resources\AdResource\Pages;

use Liberu\CRM\AdvertisingFilament\App\Resources\AdResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAd extends ViewRecord
{
    protected static string $resource = AdResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('headline'),
            TextEntry::make('status'),
            TextEntry::make('advertisingAccount.name')->label('Account'),
            TextEntry::make('campaign.name')->label('Campaign'),
            TextEntry::make('adSet.name')->label('Ad set'),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }
}
