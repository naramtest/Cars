<?php

namespace App\Services\WhatsApp\Templates;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WhatsAppTemplateService
{
    /**
     * @throws ConnectionException
     */
    public static function make(
        TemplateInterface $template
    ): PromiseInterface|Response {
        $waba_id = config("services.whatsapp.waba_id");
        $api_version = config("services.whatsapp.api_version");
        return Http::withHeaders([
            "Authorization" => "Bearer " . config("services.whatsapp.token"),
            "Content-Type" => "application/json",
        ])->post(
            "https://graph.facebook.com/{$api_version}/{$waba_id}/message_templates",
            $template->getTemplate()
        );
    }
}
