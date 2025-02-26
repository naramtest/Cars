<?php

namespace App\Filament\Resources\InspectionTypeResource\Pages;

use App\Filament\Resources\InspectionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInspectionTypes extends ManageRecords
{
    protected static string $resource = InspectionTypeResource::class;
    use ManageRecords\Concerns\Translatable;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(), Actions\LocaleSwitcher::make()];
    }
}
