@props([
    "seo",
])
<!DOCTYPE html>
<html
    lang="{{ str_replace("_", "-", app()->getLocale()) }}"
    dir="{{ app()->getLocale() == "en" ? "ltr" : "rtl" }}"
>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <meta name="csrf-token" content="{{ csrf_token() }}" />
        @if (isset($title))
            {{ $title }}
        @else
            <title>Cars</title>
        @endif

        {{ $seo ?? null }}
        {{ $graph ?? null }}
        {{ $keywords ?? null }}

        <!-- Styles -->
        @stack("styles")
        @livewireStyles

        @vite(["resources/css/app.css", "resources/js/app.js"])
        @stack("header-scripts")
    </head>

    <body class="h-full w-full antialiased">
        {{ $slot }}

        @livewireScriptConfig
        @stack("scripts")
    </body>
</html>
