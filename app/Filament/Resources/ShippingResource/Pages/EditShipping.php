<?php

namespace App\Filament\Resources\ShippingResource\Pages;

use App\Filament\Resources\ShippingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShipping extends EditRecord
{
    protected static string $resource = ShippingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Recalculate the total weight only when needed (when items have changed)
        $this->record->recalculateTotalWeight();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Make sure the total weight is up to date when loading the form
        if (isset($data["id"])) {
            $shipping = $this->getRecord();
            $shipping->recalculateTotalWeight();
        }

        return $data;
    }
}
