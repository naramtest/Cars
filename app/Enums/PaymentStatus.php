<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = "pending";
    case PROCESSING = "processing";
    case PAID = "paid";
    case FAILED = "failed";
    case CANCELED = "canceled";
    case REFUNDED = "refunded";
}
