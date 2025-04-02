<?php

use App\Enums\ReservationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("bookings", function (Blueprint $table) {
            $table->id();
            $table->string("client_name");
            $table->string("client_email");
            $table->string("client_phone");
            $table->dateTime("start_datetime");
            $table->dateTime("end_datetime");
            $table->foreignId("vehicle_id")->constrained()->restrictOnDelete();
            $table
                ->foreignId("driver_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->text("address");
            $table
                ->string("status")
                ->default(ReservationStatus::Pending->value);
            $table->text("notes")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("bookings");
    }
};
