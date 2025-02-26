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
        Schema::create("inspections", function (Blueprint $table) {
            $table->id();
            $table->foreignId("vehicle_id")->constrained()->restrictOnDelete();
            $table->string("inspection_by")->nullable();
            $table->date("inspection_date");
            $table->string("status")->default("pending");
            $table->string("repair_status")->default("pending");
            $table->text("notes")->nullable();
            $table->unsignedInteger("meter_reading_km")->nullable();
            $table->date("incoming_date")->nullable();
            $table->unsignedBigInteger("amount")->nullable();
            $table
                ->string("currency_code", 3)
                ->default(config("app.money_currency"));
            $table->string("receipt")->nullable();
            $table->json("checklist")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("inspections");
    }
};
