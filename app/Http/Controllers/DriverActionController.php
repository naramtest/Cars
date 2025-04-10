<?php

namespace App\Http\Controllers;

use App\Enums\Shipping\ShippingStatus;
use App\Models\Shipping;
use Illuminate\Http\Request;
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

    public function confirmDelivery($token)
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
                return view("shipping.delivery-failed", [
                    "message" => "The confirmation link has expired.",
                ]);
            }

            // Find the shipping
            $shipping = Shipping::find($shippingId);
            if (!$shipping) {
                return view("shipping.delivery-failed", [
                    "message" => "Shipping not found.",
                ]);
            }

            // Verify the driver matches
            if ($shipping->driver_id != $driverId) {
                return view("shipping.delivery-failed", [
                    "message" => "Unauthorized action. Driver does not match.",
                ]);
            }

            // Check if shipping is in picked up status
            if ($shipping->status !== ShippingStatus::Picked_Up) {
                return view("shipping.delivery-failed", [
                    "message" =>
                        "Invalid shipping status. Package must be picked up first.",
                ]);
            }

            // Return the form view for delivery confirmation
            return view("shipping.delivery-form", [
                "shipping" => $shipping,
                "token" => $token,
            ]);
        } catch (\Exception $e) {
            return view("shipping.delivery-failed", [
                "message" => "Invalid or expired confirmation link.",
            ]);
        }
    }

    public function processDelivery(Request $request)
    {
        try {
            // Decrypt the token
            $token = $request->input("token");
            $notes = $request->input("delivery_notes");

            $data = Crypt::decryptString($token);
            $params = json_decode($data, true);

            // Extract shipping and driver IDs
            $shippingId = $params["shipping_id"] ?? null;
            $driverId = $params["driver_id"] ?? null;

            // Find the shipping
            $shipping = Shipping::find($shippingId);
            if (!$shipping || $shipping->driver_id != $driverId) {
                return view("shipping.delivery-failed", [
                    "message" => "Invalid shipping or driver.",
                ]);
            }

            // Update shipping status to Delivered
            $shipping->update([
                "status" => ShippingStatus::Delivered->value,
                "delivered_at" => now(),
                "delivery_notes" => $notes,
            ]);

            // Return a success page
            return view("shipping.delivery-success", ["shipping" => $shipping]);
        } catch (\Exception $e) {
            return view("shipping.delivery-failed", [
                "message" => "Failed to process delivery confirmation.",
            ]);
        }
    }
}
