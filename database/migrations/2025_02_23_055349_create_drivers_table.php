<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("drivers", function (Blueprint $table) {
            $table->id();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("email")->unique();
            $table->string("phone_number");
            $table->enum("gender", Gender::cases());
            $table->date("birth_date");
            $table->text("address");
            $table->string("license_number")->unique();
            $table->date("issue_date");
            $table->date("expiration_date");
            $table->string("document");
            $table->string("license");
            $table->string("license_reference")->nullable();
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("drivers");
    }
};
