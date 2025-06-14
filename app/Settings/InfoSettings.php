<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use Spatie\Translatable\HasTranslations;

class InfoSettings extends Settings
{
    use HasTranslations;

    public array $name;
    public array $address;
    public array $about;
    public array $slogan;
    public array $phones;
    public array $emails;
    public array $socials;
    public string $support_whatsapp_number; // Add this line
    public array $admin_phones = []; // New field for admin phone numbers

    public static function group(): string
    {
        return "info";
    }

    public function getTranslatableAttributes(): array
    {
        return ["name", "address", "about", "slogan"];
    }
}
