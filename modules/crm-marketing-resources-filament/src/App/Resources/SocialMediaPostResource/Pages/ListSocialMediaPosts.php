<?php

declare(strict_types=1);

namespace Liberu\CRM\MarketingResourcesFilament\App\Resources\SocialMediaPostResource\Pages;

use Liberu\CRM\MarketingResourcesFilament\App\Resources\SocialMediaPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocialMediaPosts extends ListRecords
{
    protected static string $resource = SocialMediaPostResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
