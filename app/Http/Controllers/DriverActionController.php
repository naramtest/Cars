<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\Shipping\ShippingStatus;
use App\Models\Booking;
use App\Models\Shipping;
use Exception;
use Illuminate\Http\Request;

class DriverActionController extends Controller
{
    // Booking completion confirmation method
    public function confirmBookingCompletion(Booking $booking)
    {
        // Check if booking is in a valid state to be completed
        if (
            $booking->status !== ReservationStatus::Active &&
            $booking->status !== ReservationStatus::Confirmed
        ) {
            return view("confirm.booking.failed", [
                "message" =>
                    "This booking cannot be marked as completed in its current state.",
            ]);
        }
        // Update booking status to Completed
        $booking->update([
            "status" => ReservationStatus::Completed,
        ]);
        // Return a success page
        return view("confirm.booking.success", [
            "booking" => $booking,
        ]);
    }

    public function confirmPickup(Shipping $shipping)
    {
        try {
            // Update shipping status to Picked_Up
            $shipping->update([
                "status" => ShippingStatus::Picked_Up,
                "received_at" => now(),
            ]);

            // Return a success page
            return view("confirm.shipping.pickup-success", [
                "shipping" => $shipping,
            ]);
        } catch (Exception $e) {
            return view("confirm.shipping.pickup-failed", [
                "message" => "Invalid or expired confirmation link.",
            ]);
        }
    }

    public function confirmDelivery(Shipping $shipping)
    {
        try {
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
            ]);
        } catch (Exception $e) {
            return view("shipping.delivery-failed", [
                "message" => "Invalid or expired confirmation link.",
            ]);
        }
    }

    public function processDelivery(Request $request)
    {
        try {
            // Decrypt the token

            $notes = $request->input("delivery_notes");

            // Extract shipping and driver IDs
            $shippingId = $request->input("shipping_id");

            // Find the shipping
            $shipping = Shipping::find($shippingId);
            if (!$shipping) {
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
        } catch (Exception $e) {
            return view("shipping.delivery-failed", [
                "message" => "Failed to process delivery confirmation.",
            ]);
        }
    }
}
