<x-app-layout>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 py-12 sm:px-6 lg:px-8"
    >
        <div
            class="w-full max-w-md overflow-hidden rounded-lg bg-white shadow-lg"
        >
            <div class="flex justify-center bg-emerald-500 p-4">
                <div class="rounded-full bg-white p-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-emerald-500"
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
            </div>

            <div class="p-6">
                <div class="text-center">
                    <h2 class="mb-2 text-2xl font-bold text-gray-800">
                        Delivery Confirmed!
                    </h2>
                    <p class="mb-6 text-gray-600">
                        Thank you for confirming the delivery of this shipment.
                    </p>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <dl class="divide-y divide-gray-200">
                        <div class="flex justify-between py-3">
                            <dt class="text-sm font-medium text-gray-500">
                                Reference Number
                            </dt>
                            <dd class="text-sm font-semibold text-gray-900">
                                {{ $shipping->reference_number }}
                            </dd>
                        </div>

                        <div class="flex justify-between py-3">
                            <dt class="text-sm font-medium text-gray-500">
                                Customer
                            </dt>
                            <dd class="text-sm font-semibold text-gray-900">
                                {{ $shipping->client_name }}
                            </dd>
                        </div>

                        <div class="flex justify-between py-3">
                            <dt class="text-sm font-medium text-gray-500">
                                Delivery Address
                            </dt>
                            <dd
                                class="text-right text-sm font-semibold text-gray-900"
                            >
                                {{ $shipping->delivery_address }}
                            </dd>
                        </div>

                        <div class="flex justify-between py-3">
                            <dt class="text-sm font-medium text-gray-500">
                                Delivered At
                            </dt>
                            <dd class="text-sm font-semibold text-gray-900">
                                {{ $shipping->delivered_at->format("M j, Y g:i A") }}
                            </dd>
                        </div>

                        @if ($shipping->delivery_notes)
                            <div class="py-3">
                                <dt
                                    class="mb-1 text-sm font-medium text-gray-500"
                                >
                                    Delivery Notes
                                </dt>
                                <dd
                                    class="rounded bg-gray-50 p-3 text-sm text-gray-900"
                                >
                                    {{ $shipping->delivery_notes }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="flex justify-center space-x-3 bg-gray-50 p-4">
                <a
                    href="{{ url("/") }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        />
                    </svg>
                    Home
                </a>
                <button
                    type="button"
                    onclick="window.close()"
                    class="inline-flex items-center rounded-md border border-transparent bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    Close
                </button>
            </div>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                The customer has been notified about this delivery.
            </p>
        </div>
    </div>
</x-app-layout>
