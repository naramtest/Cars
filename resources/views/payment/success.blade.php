<x-app-layout>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="mx-auto max-w-3xl">
            <!-- Processing Card -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Header with processing icon -->
                <div class="bg-amber-500 px-4 py-5 sm:px-6">
                    <div class="flex justify-center">
                        <div class="rounded-full bg-white p-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-12 w-12 animate-spin text-amber-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Processing message -->
                <div class="px-4 py-5 text-center sm:px-6">
                    <h1 class="text-3xl font-extrabold text-gray-900">
                        Payment Processing
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Thank you for your payment. Your transaction is
                        currently being processed. Once completed, you will
                        receive a confirmation message with your invoice
                        details.
                    </p>
                    <div class="mt-4 flex justify-center">
                        <div class="flex items-center space-x-2">
                            <div
                                class="h-2 w-2 animate-bounce rounded-full bg-amber-500"
                            ></div>
                            <div
                                class="h-2 w-2 animate-bounce rounded-full bg-amber-500"
                                style="animation-delay: 0.1s"
                            ></div>
                            <div
                                class="h-2 w-2 animate-bounce rounded-full bg-amber-500"
                                style="animation-delay: 0.2s"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900">
                        Order Details
                    </h2>

                    <dl class="mt-4 divide-y divide-gray-200">
                        <div
                            class="flex justify-between py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                        >
                            <dt class="text-sm font-medium text-gray-500">
                                Reference Number
                            </dt>
                            <dd
                                class="text-right text-sm font-medium text-gray-900 sm:col-span-2 sm:mt-0 sm:text-left"
                            >
                                {{ $payment->payable->reference_number ?? "N/A" }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                        >
                            <dt class="text-sm font-medium text-gray-500">
                                Type
                            </dt>
                            <dd
                                class="text-right text-sm font-medium text-gray-900 sm:col-span-2 sm:mt-0 sm:text-left"
                            >
                                {{ class_basename($payment->payable) ?? "N/A" }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                        >
                            <dt class="text-sm font-medium text-gray-500">
                                Amount
                            </dt>
                            <dd
                                class="text-right text-sm font-medium text-gray-900 sm:col-span-2 sm:mt-0 sm:text-left"
                            >
                                {{ $payment->formatted_amount ?? "N/A" }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                        >
                            <dt class="text-sm font-medium text-gray-500">
                                Payment Date
                            </dt>
                            <dd
                                class="text-right text-sm font-medium text-gray-900 sm:col-span-2 sm:mt-0 sm:text-left"
                            >
                                {{ isset($payment->updated_at) ? $payment->updated_at->format("Y-m-d H:i") : "N/A" }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between py-3 sm:grid sm:grid-cols-3 sm:gap-4"
                        >
                            <dt class="text-sm font-medium text-gray-500">
                                Status
                            </dt>
                            <dd
                                class="mt-1 flex items-center text-sm font-medium text-amber-600 sm:col-span-2 sm:mt-0"
                            >
                                <svg
                                    class="mr-1.5 h-5 w-5 flex-shrink-0 animate-spin text-amber-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    ></path>
                                </svg>
                                Processing
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Company Info -->
                <div
                    class="border-t border-gray-200 bg-gray-50 px-4 py-5 sm:p-6"
                >
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">
                                {{ $info->name[app()->getLocale()] ?? config("app.name") }}
                            </h3>
                            <div class="mt-1 text-sm text-gray-600">
                                <p>
                                    {{ $info->address[app()->getLocale()] ?? "" }}
                                </p>
                                @if (isset($info->phones) && ! empty($info->phones))
                                    <p class="mt-2">
                                        <span class="font-medium">Phone:</span>
                                        {{ $info->phones[0]["number"] ?? "" }}
                                    </p>
                                @endif

                                @if (isset($info->emails) && ! empty($info->emails))
                                    <p>
                                        <span class="font-medium">Email:</span>
                                        {{ $info->emails[0]["email"] ?? "" }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <p class="text-xs text-gray-500">
                                {{ $info->slogan[app()->getLocale()] ?? "" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="border-t border-gray-200 px-4 py-4 sm:px-6">
                    <div class="flex justify-end space-x-3">
                        <a
                            href="{{ url("/") }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
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
                            Return Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>
                    Your payment is being processed. This may take a few
                    moments.
                </p>
                <p class="mt-1">
                    If you have any questions, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
