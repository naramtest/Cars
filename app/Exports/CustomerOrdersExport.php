<?php

namespace App\Exports;

use App\Models\Customer;
use App\Services\Currency\CurrencyService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerOrdersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles
{
    protected Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function collection()
    {
        // Get all orders from different models
        $bookings = $this->customer
            ->bookings()
            ->with(["vehicle", "driver"])
            ->get()
            ->map(function ($booking) {
                $booking->order_type = "Booking";
                return $booking;
            });

        $rents = $this->customer
            ->rents()
            ->with(["vehicle"])
            ->get()
            ->map(function ($rent) {
                $rent->order_type = "Rent";
                return $rent;
            });

        $shippings = $this->customer
            ->shippings()
            ->with(["driver", "items"])
            ->get()
            ->map(function ($shipping) {
                $shipping->order_type = "Shipping";
                return $shipping;
            });

        // Combine all orders
        return $bookings
            ->concat($rents)
            ->concat($shippings)
            ->sortByDesc("created_at");
    }

    public function map($row): array
    {
        $currencyService = app(CurrencyService::class);

        $arr = [
            "order_type" => $row->order_type,
            "reference_number" => $row->reference_number,
            "status" => $row->status->value,
            "created_at" => $row->created_at->format("Y-m-d H:i"),
        ];

        // Add type-specific data
        switch ($row->order_type) {
            case "Booking":
                $arr["vehicle"] = $row->vehicle
                    ? $row->vehicle->name .
                        " (" .
                        $row->vehicle->license_plate .
                        ")"
                    : "N/A";
                $arr["driver"] = $row->driver ? $row->driver->full_name : "N/A";
                $arr["start_date"] = $row->start_datetime
                    ? $row->start_datetime->format("Y-m-d H:i")
                    : "N/A";
                $arr["end_date"] = $row->end_datetime
                    ? $row->end_datetime->format("Y-m-d H:i")
                    : "N/A";
                $arr["pickup_address"] = $row->pickup_address;
                $arr["destination_address"] = $row->destination_address;
                $arr["total_amount"] = $row->total_price
                    ? $currencyService->format(
                        $currencyService->money($row->total_price)
                    )
                    : "N/A";
                $arr["notes"] = $row->notes;
                break;

            case "Rent":
                $arr["vehicle"] = $row->vehicle
                    ? $row->vehicle->name .
                        " (" .
                        $row->vehicle->license_plate .
                        ")"
                    : "N/A";
                $arr["driver"] = "N/A"; // Rent doesn't have driver
                $arr["start_date"] = $row->rental_start_date
                    ? $row->rental_start_date->format("Y-m-d H:i")
                    : "N/A";
                $arr["end_date"] = $row->rental_end_date
                    ? $row->rental_end_date->format("Y-m-d H:i")
                    : "N/A";
                $arr["pickup_address"] = $row->pickup_address;
                $arr["destination_address"] = $row->drop_off_address;
                $arr["total_amount"] = $row->total_price
                    ? $currencyService->format(
                        $currencyService->money($row->total_price)
                    )
                    : "N/A";
                $arr["notes"] = $row->description;
                break;

            case "Shipping":
                $arr["vehicle"] = "Shipping Service";
                $arr["driver"] = $row->driver ? $row->driver->full_name : "N/A";
                $arr["start_date"] = $row->pick_up_at
                    ? $row->pick_up_at->format("Y-m-d H:i")
                    : "N/A";
                $arr["end_date"] = $row->delivered_at
                    ? $row->delivered_at->format("Y-m-d H:i")
                    : "N/A";
                $arr["pickup_address"] = $row->pickup_address;
                $arr["destination_address"] = $row->delivery_address;
                // Shipping doesn't have total_price
                $arr["total_amount"] = "N/A";
                $arr["notes"] = $row->notes;
                break;
        }

        return $arr;
    }

    public function headings(): array
    {
        return [
            "Order Type",
            "Reference Number",
            "Status",
            "Created Date",
            "Vehicle/Service",
            "Driver",
            "Start Date",
            "End Date",
            "Pickup Address",
            "Destination Address",
            "Total Amount",
            "Notes",
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the header row
            1 => ["font" => ["bold" => true]],
        ];
    }
}
