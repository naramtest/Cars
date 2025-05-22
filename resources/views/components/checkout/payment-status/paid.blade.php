@props([
    /**@var\App\Models\Payment*/"payment",
])

<div {{ $attributes->class(["px-4 py-8 text-center"]) }}>
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
    <h2 class="mb-2 text-2xl font-bold text-gray-900">Already Paid!</h2>
    <p class="text-gray-600">
        This payment has already been completed successfully.
    </p>
    <p class="mt-2 text-sm text-gray-500">
        Amount: {{ $payment->formatted_amount }}
    </p>
</div>
