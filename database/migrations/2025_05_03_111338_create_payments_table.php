<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create("payments", function (Blueprint $table) {
            $table->id();
            $table->morphs("payable");
            $table->unsignedBigInteger("amount");
            $table->string("currency_code", 3);
            $table->string("payment_method");
            $table->string("status");
            $table->string("payment_link")->nullable();
            $table->timestamp("payment_link_expires_at")->nullable();
            $table->string("provider_id")->nullable();
            $table->json("metadata")->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("payments");
    }
};
