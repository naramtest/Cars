<?php

use App\Enums\Shipping\ShippingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("shippings", function (Blueprint $table) {
            $table->id();
            // Client Information
            $table->string("client_name");
            $table->string("client_email")->nullable();
            $table->string("client_phone");

            // Addresses
            $table->text("pickup_address");
            $table->text("delivery_address");

            // Driver Association
            $table
                ->foreignId("driver_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Tracking and Status
            $table->string("tracking_number")->unique();
            $table->string("status")->default(ShippingStatus::Pending);

            // Items
            $table->float("total_weight")->default(0);

            // Additional Notes
            $table->text("notes")->nullable();

            // Timestamps and Soft Deletes
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("shippings");
    }
};
