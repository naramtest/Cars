<?php

namespace App\Models;

use App\Enums\TemplateStatus;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public $fillable = ["name", "category", "template_id", "status"];

    public $casts = [
        "status" => TemplateStatus::class,
    ];
}
