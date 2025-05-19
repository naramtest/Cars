<?php

namespace App\Http\Controllers;

use App\Services\Payments\Webhooks\StripeWebhookService;
use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    public function stripe(Request $request)
    {
        $stripeWebhook = app(StripeWebhookService::class);
        $result = $stripeWebhook->handleWebhook($request);
        //TODO: retrun 200 for stripe
        logger($result);
    }
}
