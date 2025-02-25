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
            $table
                ->string("currency_code", 3)
                ->default(config("app.money_currency"));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("vehicles", function (Blueprint $table) {
            //
        });
    }
};
