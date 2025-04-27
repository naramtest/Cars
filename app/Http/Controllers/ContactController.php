<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Settings\InfoSettings;

class ContactController extends Controller
{
    public function whatsapp(InfoSettings $infoSettings)
    {
        return redirect()->away(
            "https://wa.me/$infoSettings->support_whatsapp_number"
        );
    }

    public function driver(Driver $driver)
    {
        return redirect()->away("https://wa.me/$driver->phone_number");
    }
}
