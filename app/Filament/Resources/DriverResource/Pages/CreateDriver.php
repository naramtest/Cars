<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data["email_verified_at"] = now();
        return parent::mutateFormDataBeforeCreate($data);
    }

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record->user;
        $role = Role::firstOrCreate(["name" => "driver"]);
        $user->assignRole($role);
    }
}
