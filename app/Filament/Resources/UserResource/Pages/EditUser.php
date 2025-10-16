<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract role_ids and sync them separately
        if (isset($data['role_ids'])) {
            $this->roleIds = $data['role_ids'];
            unset($data['role_ids']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync roles after saving
        if (isset($this->roleIds)) {
            $this->record->roles()->sync($this->roleIds);
        }
    }
}
