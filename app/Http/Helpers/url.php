<?php
if (!function_exists("templateUrl")) {
    function templateUrl(string $route): string
    {
        $templateDomain = config("services.whatsapp.production_app_base_url");
        $parsedUrl = parse_url($route);
        return $templateDomain . $parsedUrl["path"];
    }
}

if (!function_exists("templateUrlReplaceParameter")) {
    function templateUrlReplaceParameter(string $route): string
    {
        return str_replace("PLACEHOLDER_VALUE", "{{1}}", templateUrl($route));
    }
}
