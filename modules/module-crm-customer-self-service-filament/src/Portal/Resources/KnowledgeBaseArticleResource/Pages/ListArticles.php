<?php

declare(strict_types=1);

namespace Liberu\CRM\CustomerSelfServiceFilament\Portal\Resources\KnowledgeBaseArticleResource\Pages;

use Liberu\CRM\CustomerSelfServiceFilament\Portal\Resources\KnowledgeBaseArticleResource;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = KnowledgeBaseArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
