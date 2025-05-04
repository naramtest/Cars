<?php

return [
    "providers" => [
        "stripe" => [
            "key" => env("STRIPE_KEY"),
            "secret" => env("STRIPE_SECRET"),
            "webhook" => env("STRIPE_WEBHOOK_SECRET"),
            "link_expiration_days" => 7,
        ],
    ],
];
