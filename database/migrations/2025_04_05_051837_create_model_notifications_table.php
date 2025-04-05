<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("model_notifications", function (Blueprint $table) {
            $table->id();
            $table->morphs("notifiable");
            $table->string("notification_type");
            $table->timestamp("sent_at");
            $table->json("metadata")->nullable();
            $table->timestamps();

            $table->unique(
                ["notifiable_type", "notifiable_id", "notification_type"],
                "notification_unique_index"
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("model_notifications");
    }
};
