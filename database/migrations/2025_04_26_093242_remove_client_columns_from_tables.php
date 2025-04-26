<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // First check if the migration has been completed
        if (!$this->verifyMigrationCompleted()) {
            return;
        }

        // Remove columns from bookings table
        Schema::table("bookings", function (Blueprint $table) {
            $table->dropColumn(["client_name", "client_email", "client_phone"]);
        });

        // Remove columns from rents table
        Schema::table("rents", function (Blueprint $table) {
            $table->dropColumn(["client_name", "client_email", "client_phone"]);
        });

        // Remove columns from shippings table
        Schema::table("shippings", function (Blueprint $table) {
            $table->dropColumn(["client_name", "client_email", "client_phone"]);
        });
    }

    public function down(): void
    {
        // Add columns back to bookings table
        Schema::table("bookings", function (Blueprint $table) {
            $table->string("client_name")->nullable();
            $table->string("client_email")->nullable();
            $table->string("client_phone")->nullable();
        });

        // Add columns back to rents table
        Schema::table("rents", function (Blueprint $table) {
            $table->string("client_name")->nullable();
            $table->string("client_email")->nullable();
            $table->string("client_phone")->nullable();
        });

        // Add columns back to shippings table
        Schema::table("shippings", function (Blueprint $table) {
            $table->string("client_name")->nullable();
            $table->string("client_email")->nullable();
            $table->string("client_phone")->nullable();
        });
    }

    /**
     * Verify that all records have been properly migrated to the customer system
     */
    private function verifyMigrationCompleted(): bool
    {
        // Get counts of records with client data that should have been migrated
        $bookingsToMigrate = DB::table("bookings")
            ->whereNotNull("client_phone")
            ->count();

        $bookingsMigrated = DB::table("customerables")
            ->where("customerable_type", "App\\Models\\Booking")
            ->count();

        $rentsToMigrate = DB::table("rents")
            ->whereNotNull("client_phone")
            ->count();

        $rentsMigrated = DB::table("customerables")
            ->where("customerable_type", "App\\Models\\Rent")
            ->count();

        $shippingsToMigrate = DB::table("shippings")
            ->whereNotNull("client_phone")
            ->count();

        $shippingsMigrated = DB::table("customerables")
            ->where("customerable_type", "App\\Models\\Shipping")
            ->count();

        // Only proceed if all records have been migrated
        if (
            $bookingsMigrated < $bookingsToMigrate ||
            $rentsMigrated < $rentsToMigrate ||
            $shippingsMigrated < $shippingsToMigrate
        ) {
            return false;
        }

        return true;
    }
};
