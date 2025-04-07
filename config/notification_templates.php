<?php

use App\Services\WhatsApp\Admin\Booking\{ABNewHandler, ABReminderHandler};
use App\Services\WhatsApp\Admin\Rent\{ARNewHandler, ARReminderHandler};
use App\Services\WhatsApp\Customer\Booking\{CBNewHandler};
use App\Services\WhatsApp\Customer\Rent\{CREndReminderHandler, CRNewHandler};
use App\Services\WhatsApp\Driver\Booking\{DBNewHandler, DBReminderHandler, DBUpdatedHandler};

return [
    "driver" => [
        "driver_booking_new" => DBNewHandler::class,
        "driver_booking_reminder" => DBReminderHandler::class,
        "driver_booking_update" => DBUpdatedHandler::class,
    ],
    "admin" => [
        "admin_booking_new" => ABNewHandler::class,
        "admin_booking_reminder" => ABReminderHandler::class,
        //Rents
        "admin_rent_new" => ARNewHandler::class,
        "admin_rent_reminder" => ARReminderHandler::class,
    ],
    "customer" => [
        "customer_booking_new" => CBNewHandler::class,
        //Rents
        "customer_rent_new" => CRNewHandler::class,
        "customer_rent_end_reminder" => CREndReminderHandler::class,
    ],
];
