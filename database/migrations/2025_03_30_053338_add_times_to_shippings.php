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
        Schema::table("shippings", function (Blueprint $table) {
            $table->dateTime("received_at")->nullable();
            $table->dateTime("delivered_at")->nullable();
            $table->text("delivery_notes")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("shippings", function (Blueprint $table) {
            $table->dropColumn([
                "received_at",
                "delivered_at",
                "delivery_notes",
            ]);
        });
    }
};
