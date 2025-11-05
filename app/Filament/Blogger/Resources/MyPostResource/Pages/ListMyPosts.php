<?php

namespace App\Filament\Blogger\Resources\MyPostResource\Pages;

use App\Filament\Blogger\Resources\MyPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyPosts extends ListRecords
{
    protected static string $resource = MyPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create New Post'),
        ];
    }
}
