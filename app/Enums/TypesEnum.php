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
        //        TODO: add other types
        return match ($type->type) {
            self::VEHICLE => $type->vehicles()->exists(),
            self::EXPENSE => throw new \Exception("To be implemented"),
            self::INSPECTION => throw new \Exception("To be implemented"),
        };
    }
}
