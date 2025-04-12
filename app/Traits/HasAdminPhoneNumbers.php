<?php

namespace App\Traits;

use App\Settings\InfoSettings;
use Illuminate\Support\Facades\Log;
use Throwable;

trait HasAdminPhoneNumbers
{
    public function phoneNumbers($data)
    {
        try {
            $infoSettings = app(InfoSettings::class);
            $adminPhones = $infoSettings->admin_phones ?? [];

            $phoneNumbers = collect($adminPhones)
                ->filter(function ($item) {
                    // Ensure each item has a valid 'number' key
                    return is_array($item) && !empty($item["number"]);
                })
                ->pluck("number")
                ->values()
                ->toArray();

            if (empty($phoneNumbers)) {
                return ["+971562065970"]; // Fallback number
            }

            return $phoneNumbers;
        } catch (Throwable $e) {
            // Log the error
            Log::error("Error retrieving admin phone numbers", [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            // Return a fallback number in case of any error
            return ["+971562065970"];
        }
    }
}
