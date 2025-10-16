<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract role_ids and sync them separately
        if (isset($data['role_ids'])) {
            $this->roleIds = $data['role_ids'];
            unset($data['role_ids']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync roles after creating
        if (isset($this->roleIds)) {
            $this->record->roles()->sync($this->roleIds);
        }
    }
}
