<?php

namespace App\Filament\Resources\ExpenseTypeResource\Pages;

use App\Filament\Resources\ExpenseTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageExpenseTypes extends ManageRecords
{
    protected static string $resource = ExpenseTypeResource::class;
    use ManageRecords\Concerns\Translatable;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(), Actions\LocaleSwitcher::make()];
    }
}
