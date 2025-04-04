<?php

namespace App\Services\WhatsApp;

use App\Models\Template;
use App\Services\WhatsApp\Abstract\WhatsAppTemplate;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WhatsAppTemplateService
{
    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function create(WhatsAppTemplate $template): Model
    {
        //Send a Request to create a new template
        $data = $this->sendRequest(
            method: "POST",
            data: $template->facebookTemplateData()
        );

        return Template::create([
            "template_id" => $data["id"],
            "name" => $template->getTemplateId(),
            "category" => $data["category"],
            "status" => $data["status"],
        ]);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function sendRequest(
        string $method,
        string $url = null,
        array $data = []
    ) {
        $waba_id = config("services.whatsapp.waba_id");
        $api_version = config("services.whatsapp.api_version");
        $url ??= "https://graph.facebook.com/$api_version/$waba_id/message_templates";
        $baseRequest = Http::withHeaders([
            "Authorization" => "Bearer " . config("services.whatsapp.token"),
            "Content-Type" => "application/json",
        ]);
        $response = match ($method) {
            "GET", "default" => $baseRequest->get($url),
            "POST" => $baseRequest->post($url, $data),
            "PATCH" => $baseRequest->patch($url, $data),
            "DELETE" => $baseRequest->delete($url, $data),
        };
        if ($response->successful()) {
            return $response->json();
        }
        throw new Exception("Failed to create template: " . $response->body());
    }
}
