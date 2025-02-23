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
        Schema::create("typeables", function (Blueprint $table) {
            $table->id();
            $table->foreignId("type_id")->constrained()->cascadeOnDelete();
            $table->morphs("typeable");
            $table->timestamps();

            // Prevent duplicate relationships
            $table->unique(["type_id", "typeable_id", "typeable_type"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("typeables");
    }
};
