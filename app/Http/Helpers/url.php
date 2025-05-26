<?php
if (!function_exists("templateUrl")) {
    function templateUrl(string $route): string
    {
        $templateDomain = config("services.whatsapp.production_app_base_url");
        $parsedUrl = parse_url($route);

        $path = $parsedUrl["path"] ?? "";
        $query = isset($parsedUrl["query"]) ? "?" . $parsedUrl["query"] : "";

        return Str::finish($templateDomain, "/") . ltrim($path, "/") . $query;
    }
}

if (!function_exists("getQuery")) {
    function getQuery(string $route): string
    {
        $parsedUrl = parse_url($route);
        return $parsedUrl["query"] ?? "";
    }
}

if (!function_exists("templateUrlReplaceParameter")) {
    function templateUrlReplaceParameter(string $route): string
    {
        return str_replace("PLACEHOLDER_VALUE", "{{1}}", templateUrl($route));
    }
}

if (!function_exists("notDriver")) {
    function notDriver(): string
    {
        return !\Auth::user()->isDriver();
    }
}
