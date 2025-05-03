<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("payment_attempts", function (Blueprint $table) {
            $table->id();
            $table->foreignId("payment_id")->constrained()->cascadeOnDelete();
            $table->string("status");
            $table->json("provider_data")->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("payment_attempts");
    }
};
