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
        Schema::create("types", function (Blueprint $table) {
            $table->id();
            $table->json("name");
            $table->string("slug");
            $table->integer("order")->default(0)->index();
            $table->boolean("is_visible")->default(true);
            $table->json("description")->nullable();
            $table->string("type", 50);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("types");
    }
};
