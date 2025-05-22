<x-app-layout>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="mx-auto max-w-md">
            <!-- Payment Card -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Header -->
                <div class="bg-blue-500 px-4 py-5 sm:px-6">
                    <div class="flex items-center justify-center">
                        <div class="rounded-full bg-white p-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-blue-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                @if ($payment->isPaid())
                    <!-- Already Paid Message -->
                    <div class="px-4 py-8 text-center">
                        <div class="mb-4 text-emerald-500">
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
                        <h2 class="mb-2 text-2xl font-bold text-gray-900">
                            Already Paid!
                        </h2>
                        <p class="text-gray-600">
                            This payment has already been completed
                            successfully.
                        </p>
                        <p class="mt-2 text-sm text-gray-500">
                            Amount: {{ $payment->formatted_amount }}
                        </p>
                    </div>
                @elseif ($payment->status->value === "refunded")
                    <!-- Already Refunded Message -->
                    <div class="px-4 py-8 text-center">
                        <div class="mb-4 text-blue-500">
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
                                    d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"
                                />
                            </svg>
                        </div>
                        <h2 class="mb-2 text-2xl font-bold text-gray-900">
                            Payment Refunded
                        </h2>
                        <p class="text-gray-600">
                            This payment has been refunded and cannot be
                            processed again.
                        </p>
                        <p class="mt-2 text-sm text-gray-500">
                            Amount: {{ $payment->formatted_amount }}
                        </p>
                    </div>
                @else
                    <!-- Payment Form for all other statuses -->
                    <livewire:stripe-payment-component :payment="$payment" />
                @endif

                <!-- Footer -->
                <div class="border-t border-gray-200 px-4 py-4 sm:px-6">
                    <div class="flex justify-center space-x-3">
                        <a
                            href="{{ url("/") }}"
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            ← Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @unless ($payment->isPaid() || $payment->status->value === "refunded")
        @pushonce("scripts")
            <script src="https://js.stripe.com/v3/"></script>
        @endpushonce
    @endunless
</x-app-layout>
