<?php

namespace App\Models;

use App\Enums\WhatsAppTemplateStatus;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public $fillable = ["name", "category", "template_id", "status"];

    public $casts = [
        "status" => WhatsAppTemplateStatus::class,
    ];
}
