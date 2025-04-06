<?php

use App\Services\WhatsApp\Driver\Booking\{DBNewHandler, DBReminderHandler, DBUpdatedHandler};

return [
    "driver" => [
        "driver_booking_new" => DBNewHandler::class,
        "driver_booking_reminder" => DBReminderHandler::class,
        "driver_booking_update" => DBUpdatedHandler::class,
    ],
];
