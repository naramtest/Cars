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
            $table->text("note")->nullable();
            $table->dateTime("paid_at")->nullable();
            // Drop payment link related columns
            $table->dropColumn(["payment_link", "payment_link_expires_at"]);

            // Make payment_method nullable
            $table->string("payment_method")->nullable()->change();
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
            $table->dropColumn("note");
            $table->dropColumn("paid_at");
            $table->text("payment_link")->nullable();
            $table->timestamp("payment_link_expires_at")->nullable();

            // Make payment_method required again
            $table->string("payment_method")->nullable(false)->change();
        });
    }
};
