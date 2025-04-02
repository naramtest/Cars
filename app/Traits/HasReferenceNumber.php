<?php

namespace App\Traits;

trait HasReferenceNumber
{
    protected static function bootHasReferenceNumber(): void
    {
        static::creating(function ($model) {
            $prefix = $model->getReferenceNumberPrefix();

            if (empty($model->{$referenceColumn})) {
                $model->{$referenceColumn} = $model->generateReferenceNumber(
                    $prefix
                );
            }
        });
    }

    protected function getReferenceNumberPrefix(): string
    {
        return "SHP";
    }

    protected function generateReferenceNumber(
        string $prefix = "SHP",
        string $column = "reference_number"
    ): string {
        $year = now()->format("Y");
        $month = now()->format("m");

        $latestRecord = static::where(
            $column,
            "like",
            "{$prefix}-{$year}{$month}-%"
        )
            ->orderBy("id", "desc")
            ->first();

        $sequence = 1;
        if ($latestRecord) {
            $parts = explode("-", $latestRecord->{$column});
            $sequence = intval(end($parts)) + 1;
        }

        return "{$prefix}-{$year}{$month}-" .
            str_pad($sequence, 4, "0", STR_PAD_LEFT);
    }
}
