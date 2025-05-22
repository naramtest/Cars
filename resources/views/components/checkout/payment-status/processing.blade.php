@props([
    /**@var\App\Models\Payment*/"payment",
])

<div {{ $attributes->class(["px-4 py-8 text-center"]) }}>
    <div class="mb-4 text-amber-500">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="mx-auto h-16 w-16 animate-spin"
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
    <h2 class="mb-2 text-2xl font-bold text-gray-900">Payment Processing</h2>
    <p class="text-gray-600">
        Your payment is currently being processed. Please wait a moment.
    </p>
    <p class="mt-2 text-sm text-gray-500">
        Amount: {{ $payment->formatted_amount }}
    </p>
    <div class="mt-4">
        <div class="flex items-center justify-center space-x-2">
            <div class="h-2 w-2 animate-bounce rounded-full bg-amber-500"></div>
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
