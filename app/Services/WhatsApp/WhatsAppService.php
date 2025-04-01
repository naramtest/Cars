<?php

namespace App\Services\WhatsApp;

use App\Models\Booking;
use Carbon\Carbon;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppService
{
    protected $whatsappClient;

    public function __construct()
    {
        $this->whatsappClient = new WhatsAppCloudApi([
            "from_phone_number_id" => config("services.whatsapp.phone_id"),
            "access_token" => config("services.whatsapp.token"),
        ]);
    }

    /**
     * Send booking confirmation via WhatsApp
     *
     * @param Booking $booking
     * @return mixed
     */
    public function sendBookingConfirmation(
        Booking $booking,
        string $phoneNumber
    ) {
        // Format the date and time
        $formattedDate = Carbon::parse($booking->start_datetime)->format(
            "d/m/Y \@ h:i A"
        );

        // Get driver info with fallback
        $driverName = $booking->driver
            ? $booking->driver->full_name
            : "Not assigned";
        $driverContact = $booking->driver
            ? $booking->driver->phone_number
            : "Not available";

        // Get vehicle info with fallback
        $carType = $booking->vehicle
            ? $booking->vehicle->name . " " . $booking->vehicle->model
            : "Not assigned";
        $licensePlate = $booking->vehicle
            ? $booking->vehicle->license_plate
            : "Not available";

        // Components for the template
        $component_body = [
            // Parameter 1: Date and time
            ["type" => "text", "text" => $formattedDate],

            // Parameter 2: Address (combined pickup and drop-off)
            ["type" => "text", "text" => $booking->address],

            // Parameter 3: Driver name
            ["type" => "text", "text" => $driverName],

            // Parameter 4: Driver contact
            ["type" => "text", "text" => $driverContact],

            // Parameter 5: Car type
            ["type" => "text", "text" => $carType],

            // Parameter 6: License plate
            ["type" => "text", "text" => $licensePlate],
        ];
        $components = new Component([], $component_body, []);

        try {
            // Send message using template
            return $this->whatsappClient->sendTemplate(
                $phoneNumber,
                "booking_confirmation_admin", // Template name as registered on WhatsApp Business
                "en_US", // Language
                $components
            );
        } catch (\Exception $e) {
            \Log::error("WhatsApp API Error: " . $e->getMessage());
            return false;
        }
    }
}
