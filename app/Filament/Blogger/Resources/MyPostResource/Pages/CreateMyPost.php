<?php

namespace App\Filament\Blogger\Resources\MyPostResource\Pages;

use App\Filament\Blogger\Resources\MyPostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyPost extends CreateRecord
{
    protected static string $resource = MyPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the author to the current blogger
        $data['author_id'] = Auth::id();
        $data['moderation_status'] = 'approved'; // Bloggers are trusted

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
