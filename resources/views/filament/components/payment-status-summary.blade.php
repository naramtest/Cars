@php
    $record = $getRecord();
    $paymentStatus = $record ? $record->getPaymentStatusArray() : null;
@endphp

@if ($record && $paymentStatus)
    <div class="space-y-4">
        <!-- Payment Progress Bar -->
        <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
            <div
                class="bg-success-600 h-2.5 rounded-full"
                style="width: {{ $paymentStatus["payment_percentage"] }}%"
            ></div>
        </div>

        <!-- Payment Summary -->
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Total Price</p>
                <p class="font-medium">
                    {{ $paymentStatus["formatted_total"] }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Total Paid</p>
                <p
                    class="{{ $paymentStatus["is_fully_paid"] ? "text-success-600 dark:text-success-400" : "" }} font-medium"
                >
                    {{ $paymentStatus["formatted_paid"] }}
                </p>
            </div>
        </div>

        <!-- Payment Status Badge -->
        <div class="flex items-center">
            <span
                class="{{ $paymentStatus["is_fully_paid"] ? "bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200" : "bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200" }} inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
            >
                @if ($paymentStatus["is_fully_paid"])
                    <svg
                        class="mr-1.5 h-3 w-3"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                    Fully Paid
                @else
                    <svg
                        class="mr-1.5 h-3 w-3"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                    {{ $paymentStatus["payment_percentage"] }}% Paid
                @endif
            </span>
        </div>
    </div>
@else
    <div class="italic text-gray-500">
        Payment information will be available after saving
    </div>
@endif
