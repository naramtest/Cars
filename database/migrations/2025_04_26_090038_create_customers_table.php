<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("customers", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email")->nullable();
            $table->string("phone_number")->index(); // Add index for faster lookups
            $table->text("notes")->nullable(); // Optional: useful for customer-specific notes
            $table->timestamps();
            $table->softDeletes(); // Include soft deletes
        });
        Schema::create("customerables", function (Blueprint $table) {
            $table->id();
            $table->foreignId("customer_id")->constrained()->cascadeOnDelete();
            $table->morphs("customerable");
            $table->timestamps();

            // Create a unique constraint to prevent duplicates
            // Create a unique constraint with a shorter name
            $table->unique(
                ["customer_id", "customerable_id", "customerable_type"],
                "customer_relation_unique" // Shorter custom name for the constraint
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("customers");
        Schema::dropIfExists("customerables");
    }
};
