@props([
    /**@var\App\Models\Payment*/"payment",
])

<div {{ $attributes->class(["px-4 py-8 text-center"]) }}>
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
    <h2 class="mb-2 text-2xl font-bold text-gray-900">Payment Refunded</h2>
    <p class="text-gray-600">
        This payment has been refunded and cannot be processed again.
    </p>
    <p class="mt-2 text-sm text-gray-500">
        Amount: {{ $payment->formatted_amount }}
    </p>
</div>
