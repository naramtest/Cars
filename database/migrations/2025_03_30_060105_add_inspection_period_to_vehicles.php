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
        Schema::table("vehicles", function (Blueprint $table) {
            $table->unsignedInteger("inspection_period_days")->nullable();
            $table->boolean("notify_before_inspection")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("vehicles", function (Blueprint $table) {
            $table->dropColumn([
                "inspection_period_days",
                "notify_before_inspection",
            ]);
        });
    }
};
