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
        Schema::create("rents", function (Blueprint $table) {
            $table->id();
            $table->string("rent_number")->unique();
            $table->string("client_name")->nullable();
            $table->string("client_email")->nullable();
            $table->string("client_phone")->nullable();
            $table->dateTime("rental_start_date");
            $table->dateTime("rental_end_date")->nullable();
            $table->text("pickup_address");
            $table->text("drop_off_address");
            $table
                ->string("status")
                ->default(ReservationStatus::Pending->value);
            $table->text("terms_conditions")->nullable();
            $table->text("description")->nullable();
            $table->foreignId("vehicle_id")->constrained()->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rents");
    }
};
