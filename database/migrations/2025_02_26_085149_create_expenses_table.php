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
        Schema::create("expenses", function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table
                ->foreignId("vehicle_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->date("expense_date");
            $table->unsignedBigInteger("amount"); // Integer storage for Money
            $table
                ->string("currency_code", 3)
                ->default(config("app.money_currency"));
            $table->string("receipt")->nullable();
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
        Schema::dropIfExists("expenses");
    }
};
