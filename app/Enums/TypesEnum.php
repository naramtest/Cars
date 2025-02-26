<?php

namespace App\Enums;

use App\Models\Type;

enum TypesEnum: string
{
    case VEHICLE = "Vehicle";
    case EXPENSE = "Expense";
    case INSPECTION = "Inspection";

    public static function getExists(Type $type): ?bool
    {
        return match ($type->type) {
            self::VEHICLE => $type->vehicles()->exists(),
            self::EXPENSE => $type->expenses()->exists(),
            self::INSPECTION => $type->inspections()->exists(),
        };
    }
}
