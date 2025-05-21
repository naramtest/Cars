<?php

namespace App\Exceptions;

use Exception;

class PaymentProcessException extends Exception
{
    public function __construct(
        protected string $title,
        string $message,
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getNotificationTitle(): string
    {
        return $this->title;
    }
}
