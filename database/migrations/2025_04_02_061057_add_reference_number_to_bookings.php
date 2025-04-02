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
            $table->string("reference_number")->unique();
        });

        Schema::table("rents", function (Blueprint $table) {
            $table->renameColumn("rent_number", "reference_number");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("bookings", function (Blueprint $table) {
            $table->dropColumn("reference_number");
        });
    }
};
