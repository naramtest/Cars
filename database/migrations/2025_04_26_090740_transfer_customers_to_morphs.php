<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Process bookings
        $this->processModel("bookings", "App\\Models\\Booking");

        // Process rents
        $this->processModel("rents", "App\\Models\\Rent");

        // Process shippings
        $this->processModel("shippings", "App\\Models\\Shipping");
    }

    private function processModel($table, $modelClass): void
    {
        $records = DB::table($table)
            ->whereNotNull("client_name")
            ->whereNotNull("client_phone")
            ->get();

        foreach ($records as $record) {
            // Find or create customer by phone number
            $customerId = $this->findOrCreateCustomer(
                $record->client_name,
                $record->client_email ?? null,
                $record->client_phone
            );

            // Create morph relation if it doesn't exist
            if (
                !DB::table("customerables")
                    ->where("customerable_id", $record->id)
                    ->where("customerable_type", $modelClass)
                    ->where("customer_id", $customerId)
                    ->exists()
            ) {
                DB::table("customerables")->insert([
                    "customer_id" => $customerId,
                    "customerable_id" => $record->id,
                    "customerable_type" => $modelClass,
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);
            }
        }
    }

    /**
     * Find an existing customer by phone number or create a new one
     */
    private function findOrCreateCustomer($name, $email, $phoneNumber): int
    {
        // Clean the phone number to ensure consistent format
        $phoneNumber = preg_replace("/\s+/", "", $phoneNumber);

        // Look for existing customer by phone
        $customerId = DB::table("customers")
            ->where("phone_number", $phoneNumber)
            ->value("id");

        if (!$customerId) {
            // Create new customer if none exists
            $customerId = DB::table("customers")->insertGetId([
                "name" => $name,
                "email" => $email,
                "phone_number" => $phoneNumber,
                "created_at" => now(),
                "updated_at" => now(),
            ]);
        }

        return $customerId;
    }

    public function down(): void
    {
        // No need to reverse this data migration
    }
};
