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
        Schema::create("shipping_items", function (Blueprint $table) {
            $table->id();
            // Relationship to Shipping
            $table->foreignId("shipping_id")->constrained()->cascadeOnDelete();

            // Item Details
            $table->string("name");
            $table->unsignedInteger("quantity")->default(1);
            $table->float("weight")->default(0);
            $table->text("description")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("shipping_items");
    }
};
