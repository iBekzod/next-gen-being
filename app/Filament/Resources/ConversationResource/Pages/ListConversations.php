<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use Filament\Resources\Pages\ListRecords;

class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;
}
