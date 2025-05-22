<?php

return [
    "providers" => [
        "stripe" => [
            "key" => env("STRIPE_KEY"),
            "secret" => env("STRIPE_SECRET"),
            "webhook" => env("STRIPE_WEBHOOK_SECRET"),
            "link_expiration_days" => 7,
            "elements" => [
                "theme" => env("STRIPE_ELEMENTS_THEME", "stripe"),
                "currency" => env("STRIPE_ELEMENTS_CURRENCY", "usd"),
            ],
        ],
    ],
];
