// config/payment.php
<?php return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment provider that will be used for
    | generating payment links. You can change this to a different provider
    | supported by your application.
    |
    */
    "provider" => env("PAYMENT_PROVIDER", "stripe"),

    /*
    |--------------------------------------------------------------------------
    | Payment Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each payment provider your
    | application supports.
    |
    */
    "providers" => [
        "stripe" => [
            "secret" => env("STRIPE_SECRET"),
            "webhook_secret" => env("STRIPE_WEBHOOK_SECRET"),
            "link_expiration_days" => 7,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Statuses
    |--------------------------------------------------------------------------
    |
    | These are the default statuses used in the payment system.
    |
    */
    "statuses" => [
        "pending" => "pending",
        "paid" => "paid",
        "failed" => "failed",
        "canceled" => "canceled",
        "refunded" => "refunded",
    ],
];
