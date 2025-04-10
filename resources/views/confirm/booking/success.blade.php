<x-app-layout>
    <main class="flex min-h-screen items-center justify-center bg-gray-100">
        <div
            class="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-md"
        >
            <div class="mb-4 text-green-500">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mx-auto h-16 w-16"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
            </div>
            <h1 class="mb-4 text-2xl font-bold">Booking Completed!</h1>
            <p class="mb-4">
                You have successfully marked this booking as complete:
                <strong>{{ $booking->reference_number }}</strong>
            </p>
            <p class="mb-6 text-sm text-gray-600">
                The booking status has been updated to "Completed" and the
                timestamp has been recorded.
            </p>
            <div class="rounded-lg bg-gray-100 p-4">
                <p class="text-sm text-gray-700">
                    <strong>Client:</strong>
                    {{ $booking->client_name }}
                </p>
                <p class="text-sm text-gray-700">
                    <strong>Vehicle:</strong>
                    {{ $booking->vehicle->name ?? "N/A" }}
                    ({{ $booking->vehicle->license_plate ?? "N/A" }})
                </p>
                <p class="text-sm text-gray-700">
                    <strong>Duration:</strong>
                    {{ $booking->start_datetime->format("M j, Y H:i") }}
                    to {{ $booking->end_datetime->format("M j, Y H:i") }}
                </p>
                <p class="text-sm text-gray-700">
                    <strong>Pickup:</strong>
                    {{ $booking->pickup_address }}
                </p>
            </div>
            <p class="mt-6 text-sm text-gray-500">
                Thank you for your service! The booking has been successfully
                completed.
            </p>
        </div>
    </main>
</x-app-layout>
