<?php

namespace App\Services\WhatsApp;

use App\Enums\TemplateStatus;
use App\Models\Template;
use App\Services\WhatsApp\Abstract\WhatsAppTemplate;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WhatsAppTemplateService
{
    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function resolveTemplate(
        string|WhatsAppTemplate $templateClassName
    ): Template {
        $templateClass = HandlerResolver::resolve($templateClassName);
        $templateName = $templateClass->getTemplateName();
        $template = Template::where("name", $templateName)->first();

        //If No Found or not Approved request or create template on the facebook dashboard
        if (!$template or $template->status !== TemplateStatus::APPROVED) {
            $template = $this->syncByNameFromRemote($templateName);
            if (!$template) {
                $template = $this->createRemoteTemplate($templateClass);
            }
        }
        if ($template->status === TemplateStatus::APPROVED) {
            return $template;
        }
        throw new Exception(
            "Template '$templateName' is still pending approval."
        );
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function syncByNameFromRemote($templateName): ?Template
    {
        $response = $this->makeHttpRequest("GET", null, [
            "name" => $templateName,
        ]);

        return empty($response["data"])
            ? null
            : $this->saveFromRemoteData($response["data"][0]);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function makeHttpRequest(
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
            "GET", "default" => $baseRequest->get($url, $data),
            "POST" => $baseRequest->post($url, $data),
            "PATCH" => $baseRequest->patch($url, $data),
            "DELETE" => $baseRequest->delete($url, $data),
        };
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

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function createRemoteTemplate(WhatsAppTemplate $template): Template
    {
        //Send a Request to create a new template
        $data = $this->makeHttpRequest(
            method: "POST",
            data: $template->facebookTemplateData()
        );
        $data["name"] = $template->getTemplateName();
        return $this->saveFromRemoteData($data);
    }

    /**
     * @throws ConnectionException
     */
    public function syncAllFromRemote(): void
    {
        //TODO: check if there is a paginate
        $response = $this->makeHttpRequest("GET");
        $templates = $response["data"];
        foreach ($templates as $template) {
            $this->saveFromRemoteData($template);
        }
    }
}
