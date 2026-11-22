<?php

declare(strict_types=1);

namespace Liberu\CRM\KnowledgeFilament\App\Resources\KnowledgeBaseArticleResource\Pages;

use Liberu\CRM\KnowledgeFilament\App\Resources\KnowledgeBaseArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeBaseArticle extends CreateRecord
{
    protected static string $resource = KnowledgeBaseArticleResource::class;
}
