<?php

namespace App\Filament\Resources\AddonResource\Pages;

use App\Filament\Resources\AddonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAddons extends ManageRecords
{
    protected static string $resource = AddonResource::class;

    use ManageRecords\Concerns\Translatable;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(), Actions\LocaleSwitcher::make()];
    }
}
