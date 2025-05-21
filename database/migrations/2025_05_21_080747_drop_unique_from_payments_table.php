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
        Schema::table("payments", function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique("payments_payable_unique");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("payments", function (Blueprint $table) {
            // Add the constraint back during rollback
            $table->unique(
                ["payable_type", "payable_id"],
                "payments_payable_unique"
            );
        });
    }
};
