<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class TokenHelper
{
    public static function generatePickupToken(
        $shippingId,
        $driverId,
        $pickupTime
    ): string {
        $data = [
            "shipping_id" => $shippingId,
            "driver_id" => $driverId,
            "timestamp" => $pickupTime,
        ];

        return Crypt::encryptString(json_encode($data));
    }
}
