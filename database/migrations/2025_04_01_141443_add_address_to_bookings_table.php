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
        Schema::table("bookings", function (Blueprint $table) {
            // Rename existing address column to pickup_address
            $table->renameColumn("address", "pickup_address");

            // Add new destination_address column
            $table->text("destination_address")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("bookings", function (Blueprint $table) {
            // Undo changes in reverse order
            $table->renameColumn("pickup_address", "address");
            $table->dropColumn("destination_address");
        });
    }
};
