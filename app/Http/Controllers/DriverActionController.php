<?php

namespace App\Http\Controllers;

use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use Illuminate\Support\Facades\Crypt;

class DriverActionController extends Controller
{
    public function confirmPickup($token)
    {
        try {
            // Decrypt the token
            $data = Crypt::decryptString($token);
            $params = json_decode($data, true);

            // Extract shipping and driver IDs
            $shippingId = $params["shipping_id"] ?? null;
            $driverId = $params["driver_id"] ?? null;
            $pickupTime = $params["pickup_time"] ?? null;

            if ($pickupTime and now()->isAfter($pickupTime->addDay())) {
                return view("confirm.shipping.pickup-failed", [
                    "message" => "The confirmation link has expired.",
                ]);
            }

            // Find the shipping
            $shipping = Shipping::find($shippingId);
            if (!$shipping) {
                return view("confirm.shipping.pickup-failed", [
                    "message" => "Shipping not found.",
                ]);
            }

            // Verify the driver matches
            if ($shipping->driver_id != $driverId) {
                return view("confirm.shipping.pickup-failed", [
                    "message" => "Unauthorized action. Driver does not match.",
                ]);
            }

            // Update shipping status to Picked_Up
            $shipping->update([
                "status" => ShippingStatus::Picked_Up->value,
                "received_at" => now(),
            ]);

            // Return a success page
            return view("confirm.shipping.pickup-success", [
                "shipping" => $shipping,
            ]);
        } catch (\Exception $e) {
            return view("confirm.shipping.pickup-failed", [
                "message" => "Invalid or expired confirmation link.",
            ]);
        }
    }
}
