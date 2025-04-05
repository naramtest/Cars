<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelNotification extends Model
{
    protected $fillable = ["notification_type", "sent_at", "metadata"];

    protected $casts = [
        "sent_at" => "datetime",
        "metadata" => "array",
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
