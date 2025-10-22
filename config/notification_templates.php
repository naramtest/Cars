<?php

use App\Services\WhatsApp\Admin\Booking\{ABNewHandler, ABReminderHandler};
use App\Services\WhatsApp\Admin\Rent\{ARNewHandler, ARReminderHandler};
use App\Services\WhatsApp\Admin\Shipping\{ASDeliveredHandler, ASNewHandler, ASReminderHandler};
use App\Services\WhatsApp\Admin\Vehicle\{VehicleInspectionReminderHandler, VehicleRegistrationExpiryHandler};
use App\Services\WhatsApp\Customer\Booking\{CBNewHandler, CBUpdateHandler};
use App\Services\WhatsApp\Customer\Payment\{CInvoiceDownloadHandler, CPaymentLinkHandler};
use App\Services\WhatsApp\Customer\Rent\{CREndReminderHandler, CRFineReminderHandler, CRNewHandler, CRUpdateHandler};
use App\Services\WhatsApp\Customer\Shipping\{CSDeliveredHandler, CSNewHandler, CSPickedUpHandler, CSUpdateHandler};
use App\Services\WhatsApp\Driver\Booking\{DBNewHandler, DBReminderHandler, DBUpdatedHandler};
use App\Services\WhatsApp\Driver\Shipping\{DSDeliveryHandler, DSNewHandler, DSReminderHandler};
use App\Services\WhatsApp\Driver\Vehicle\{DVInspectionReminderHandler};

return [
    "driver" => [
        "driver_booking_new" => DBNewHandler::class,
        "driver_booking_reminder" => DBReminderHandler::class,
        "driver_booking_update" => DBUpdatedHandler::class,

        //Vehicle
        "driver_vehicle_inspection_reminder" =>
            DVInspectionReminderHandler::Class,

        //Shipping
        "driver_shipping_confirmed" => DSNewHandler::class,
        "driver_shipping_reminder" => DSReminderHandler::class,
        "driver_shipping_delivery" => DSDeliveryHandler::class,
    ],
    "admin" => [
        "admin_booking_new" => ABNewHandler::class,
        "admin_booking_reminder" => ABReminderHandler::class,
        //Rents
        "admin_rent_new" => ARNewHandler::class,
        "admin_rent_reminder" => ARReminderHandler::class,

        //Vehicle
        "admin_vehicle_inspection_reminder" =>
            VehicleInspectionReminderHandler::Class,
        "admin_vehicle_registration_expiry_reminder" =>
            VehicleRegistrationExpiryHandler::Class,

        //Shipping
        "admin_shipping_new" => ASNewHandler::class,
        "admin_shipping_reminder" => ASReminderHandler::class,
        "admin_shipping_delivered" => ASDeliveredHandler::class,
        "admin_payment_success" =>
            App\Services\WhatsApp\Admin\Payment\APaymentSuccessHandler::class,
    ],
    "customer" => [
        "customer_booking_new" => CBNewHandler::class,
        "customer_booking_update" => CBUpdateHandler::class,
        //Rents
        "customer_rent_new" => CRNewHandler::class,
        "customer_rent_update" => CRUpdateHandler::class,
        "customer_rent_end_reminder" => CREndReminderHandler::class,
        //Shipping
        "customer_shipping_confirmed" => CSNewHandler::class,
        "customer_shipping_picked_up" => CSPickedUpHandler::class,
        "customer_shipping_delivered" => CSDeliveredHandler::class,
        "customer_shipping_update" => CSUpdateHandler::class,

        //Payment
        "customer_payment_link" => CPaymentLinkHandler::class,
        "customer_rent_fine_reminder" => CRFineReminderHandler::class,


        "customer_invoice_download" => CInvoiceDownloadHandler::class, // Add this line
    ],
];
