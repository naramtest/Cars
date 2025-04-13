<?php

namespace App\Services\WhatsApp;

use App\Models\Template;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WhatsAppUpdateTemplateService
{
    /**
     * @throws ConnectionException
     */
    public function updateTemplate(string|WhatsAppAbstractHandler $handlerClass)
    {
        $templateClass = HandlerResolver::resolve($handlerClass);
        $templateName = $templateClass->getTemplateName();
        $template = Template::where("name", $templateName)->first();
        $api_version = config("services.whatsapp.api_version");
        $response = $this->makeHttpRequest(
            "https://graph.facebook.com/$api_version/$template->template_id",
            data: $templateClass->facebookTemplateData()
        );

        dd($response);
        return empty($response["data"])
            ? null
            : $this->saveFromRemoteData($response["data"][0]);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function makeHttpRequest(string $url = null, array $data = [])
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . config("services.whatsapp.token"),
            "Content-Type" => "application/json",
        ])->post($url, $data);

        if ($response->successful()) {
            return $response->json();
        }
        throw new Exception("Failed to create template: " . $response->body());
    }

    public function saveFromRemoteData(array $data): Template
    {
        return Template::updateOrCreate(
            [
                "template_id" => $data["id"],
            ],
            [
                "name" => $data["name"],
                "status" => $data["status"],
                "category" => $data["category"],
            ]
        );
    }
}
