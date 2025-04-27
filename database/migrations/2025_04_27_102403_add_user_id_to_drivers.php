<?php

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add user_id column to drivers table
        Schema::table("drivers", function (Blueprint $table) {
            $table
                ->foreignId("user_id")
                ->nullable()
                ->after("id")
                ->constrained()
                ->nullOnDelete();
        });

        // Create users for existing drivers
        $drivers = Driver::all();
        foreach ($drivers as $driver) {
            // Create user with driver's information
            $user = User::create([
                "name" => $driver->first_name . " " . $driver->last_name,
                "email" => $driver->email,
                "password" => Hash::make("123123123"),
                "email_verified_at" => now(),
            ]);

            // Associate driver with the new user
            $driver->user_id = $user->id;
            $driver->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set user_id to null on all drivers to avoid foreign key constraint issues
        DB::table("drivers")->update(["user_id" => null]);

        // Remove user_id column from drivers table
        Schema::table("drivers", function (Blueprint $table) {
            $table->dropConstrainedForeignId("user_id");
        });
    }
};
