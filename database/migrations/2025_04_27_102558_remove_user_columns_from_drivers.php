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
        Schema::table("drivers", function (Blueprint $table) {
            $table->dropColumn(["first_name", "last_name", "email"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("drivers", function (Blueprint $table) {
            $table->string("first_name")->after("user_id")->nullable();
            $table->string("last_name")->after("first_name")->nullable();
            $table->string("email")->after("last_name")->nullable();
        });
    }
};
