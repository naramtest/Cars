<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateVehicleInspectionDates extends Command
{
    protected $signature = "vehicles:update-inspection-dates";
    protected $description = "Update next inspection dates for all vehicles";

    public function handle()
    {
        $this->info("Updating vehicle inspection dates...");

        DB::beginTransaction();
        try {
            // Get all vehicles that need updates
            $vehicles = Vehicle::whereNull("next_inspection_date")
                ->orWhere("next_inspection_date", "<", now())
                ->get();

            $updatedCount = 0;
            foreach ($vehicles as $vehicle) {
                // If next_inspection_date is not set, initialize it based on last inspection or creation date
                if (!$vehicle->next_inspection_date) {
                    $lastInspection = $vehicle
                        ->inspections()
                        ->latest()
                        ->first();
                    $startDate = $lastInspection
                        ? $lastInspection->inspection_date
                        : $vehicle->created_at;
                    $vehicle->next_inspection_date = $startDate
                        ->copy()
                        ->addDays($vehicle->inspection_period_days ?? 0);
                    $updatedCount++;
                }
                // If next_inspection_date is in the past, calculate a new future date
                elseif (
                    $vehicle->next_inspection_date->isPast() and
                    $vehicle->inspection_period_days
                ) {
                    // Calculate how many periods have passed
                    $now = Carbon::now();
                    $daysSinceLastInspection = $vehicle->next_inspection_date->diffInDays(
                        $now
                    );
                    $periodsToAdd = ceil(
                        $daysSinceLastInspection /
                            $vehicle->inspection_period_days
                    );

                    // Add enough periods to push the date into the future
                    $vehicle->next_inspection_date = $vehicle->next_inspection_date
                        ->copy()
                        ->addDays(
                            $periodsToAdd != 0
                                ? $periodsToAdd *
                                    $vehicle->inspection_period_days
                                : $vehicle->inspection_period_days ?? 0
                        );
                    $updatedCount++;
                }

                $vehicle->save();
            }

            DB::commit();
            $this->info(
                "Updated next inspection dates for {$updatedCount} vehicles."
            );
            return 0;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(
                "Failed to update vehicle inspection dates: " . $e->getMessage()
            );
            $this->error(
                "Error updating vehicle inspection dates: " . $e->getMessage()
            );
            return 1;
        }
    }
}
