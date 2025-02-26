<?php

namespace App\Filament\Widgets;

use App\Enums\Booking\BookingStatus;
use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BookingChartsWidget extends ChartWidget
{
    protected static ?string $heading = "Booking Charts";

    protected static ?int $sort = 2;
    protected static ?string $maxHeight = "300px";
    protected int|string|array $columnSpan = "full";

    // Properties to store chart data
    protected array $monthLabels = [];
    protected array $monthData = [];
    protected array $vehicleLabels = [];
    protected array $vehicleData = [];

    // Add filterable support

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = "monthly";
    }

    protected function getData(): array
    {
        // Get booking counts by month for the current year
        $bookingsByMonth = Booking::selectRaw(
            "MONTH(created_at) as month, COUNT(*) as count"
        )
            ->whereYear("created_at", Carbon::now()->year)
            ->groupBy("month")
            ->orderBy("month")
            ->get()
            ->keyBy("month");

        // Get status distribution
        $statusData = [];
        foreach (BookingStatus::cases() as $status) {
            $count = Booking::where("status", $status)->count();
            $statusData[$status->getLabel()] = $count;
        }

        // Get most booked vehicles (top 5)
        $vehicleBookings = Booking::select("vehicle_id")
            ->selectRaw("COUNT(*) as booking_count")
            ->groupBy("vehicle_id")
            ->orderByDesc("booking_count")
            ->limit(5)
            ->get();

        $this->vehicleLabels = [];
        $this->vehicleData = [];

        foreach ($vehicleBookings as $vBooking) {
            $vehicle = Vehicle::find($vBooking->vehicle_id);
            $this->vehicleLabels[] = $vehicle ? $vehicle->name : "Unknown";
            $this->vehicleData[] = $vBooking->booking_count;
        }

        // Prepare month labels and datasets
        $this->monthLabels = [];
        $this->monthData = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create(null, $i, 1)->format("M");
            $this->monthLabels[] = $monthName;
            $this->monthData[] = isset($bookingsByMonth[$i])
                ? $bookingsByMonth[$i]->count
                : 0;
        }

        // Return data for the current filter
        return $this->getChartData();
    }

    protected function getChartData(): array
    {
        if ($this->filter === "vehicles") {
            return [
                "datasets" => [
                    [
                        "label" => __("dashboard.Most Booked Vehicles"),
                        "data" => $this->vehicleData,
                        "backgroundColor" => [
                            "rgba(255, 99, 132, 0.7)",
                            "rgba(54, 162, 235, 0.7)",
                            "rgba(255, 206, 86, 0.7)",
                            "rgba(75, 192, 192, 0.7)",
                            "rgba(153, 102, 255, 0.7)",
                        ],
                        "borderColor" => [
                            "rgba(255, 99, 132, 1)",
                            "rgba(54, 162, 235, 1)",
                            "rgba(255, 206, 86, 1)",
                            "rgba(75, 192, 192, 1)",
                            "rgba(153, 102, 255, 1)",
                        ],
                        "borderWidth" => 1,
                    ],
                ],
                "labels" => $this->vehicleLabels,
            ];
        }

        // Default to monthly view
        return [
            "datasets" => [
                [
                    "label" => __("dashboard.Bookings"),
                    "data" => $this->monthData,
                    "backgroundColor" => "rgba(255, 165, 0, 0.7)",
                    "borderColor" => "rgb(255, 165, 0)",
                    "borderWidth" => 1,
                ],
            ],
            "labels" => $this->monthLabels,
        ];
    }

    // Add a tab-like interface to switch between different charts

    protected function getType(): string
    {
        return "bar";
    }

    protected function getOptions(): array
    {
        return [
            "plugins" => [
                "legend" => [
                    "display" => true,
                    "position" => "top",
                ],
                "tooltip" => [
                    "mode" => "index",
                    "intersect" => false,
                ],
            ],
            "scales" => [
                "y" => [
                    "beginAtZero" => true,
                    "title" => [
                        "display" => true,
                        "text" => __("dashboard.Number of Bookings"),
                    ],
                ],
            ],
            "responsive" => true,
            "maintainAspectRatio" => false,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            "monthly" => "Monthly Bookings",
            "vehicles" => "Top Vehicles",
        ];
    }
}
