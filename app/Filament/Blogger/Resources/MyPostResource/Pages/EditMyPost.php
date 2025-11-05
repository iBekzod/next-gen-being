<?php

namespace App\Filament\Blogger\Resources\MyPostResource\Pages;

use App\Filament\Blogger\Resources\MyPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyPost extends EditRecord
{
    protected static string $resource = MyPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
