<?php

namespace App\Filament\Resources\CollectionPostResource\Pages;

use App\Filament\Resources\CollectionPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollectionPosts extends ListRecords
{
    protected static string $resource = CollectionPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
