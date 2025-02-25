<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("booking_addon", function (Blueprint $table) {
            $table->id();
            $table->foreignId("booking_id")->constrained()->cascadeOnDelete();
            $table->foreignId("addon_id")->constrained()->cascadeOnDelete();
            $table->unsignedInteger("quantity")->default(1);
            $table->timestamps();

            // Prevent duplicate relationships
            $table->unique(["booking_id", "addon_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("booking_addon");
    }
};
