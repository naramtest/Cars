<?php

use App\Enums\Vehicle\FuelType;
use App\Enums\Vehicle\GearboxType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("vehicles", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("model");
            $table->string("engine_number")->unique();
            $table->string("engine_type");
            $table->string("license_plate")->unique();
            $table->date("registration_expiry_date");
            $table->decimal("daily_rate", 10, 2);
            $table->date("year_of_first_immatriculation");
            $table->enum(
                "gearbox",
                array_column(GearboxType::cases(), "value")
            );
            $table->enum("fuel_type", array_column(FuelType::cases(), "value"));
            $table->unsignedInteger("number_of_seats");
            $table->unsignedInteger("kilometer");
            $table->json("options")->nullable();
            $table->string("document")->nullable();
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
        Schema::dropIfExists("vehicles");
    }
};
