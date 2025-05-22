<!-- Payment Details -->
<div class="px-4 py-5 sm:p-6">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Complete Payment</h1>
        <p class="mt-2 text-gray-600">
            Enter your card details to complete the payment
        </p>
    </div>

    <!-- Order Summary -->
    <div class="mb-6 border-t border-gray-200 pt-4">
        <dl class="divide-y divide-gray-200">
            <div class="flex justify-between py-2">
                <dt class="text-sm font-medium text-gray-500">Order</dt>
                <dd class="text-sm font-medium text-gray-900">
                    {{ class_basename($payment->payable) }}
                    #{{ $payment->payable->reference_number ?? $payment->payable->id }}
                </dd>
            </div>
            <div class="flex justify-between py-2">
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="text-sm font-medium text-gray-900">
                    {{ $payment->formatted_amount }}
                </dd>
            </div>
            @if ($payment->note)
                <div class="py-2">
                    <dt class="mb-1 text-sm font-medium text-gray-500">Note</dt>
                    <dd class="text-sm text-gray-700">
                        {{ $payment->note }}
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <!-- Payment Form -->
    <div wire:submit="pay" id="payment-form-container">
        <form id="payment-form" class="space-y-4">
            <!-- Card Element will be mounted here -->
            <x-checkout.stripe-payment-element :total="$payment->amount" />

            <!-- Card errors will be displayed here -->
            <div
                id="card-errors"
                role="alert"
                class="text-sm text-red-600"
            ></div>

            <!-- Submit Button -->
            <button
                type="submit"
                id="submit-button"
                class="flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                wire:loading.attr="disabled"
                wire:target="setProcessing"
            >
                <span wire:loading.remove wire:target="setProcessing">
                    Pay {{ $payment->formatted_amount }}
                </span>
                <span
                    wire:loading
                    wire:target="setProcessing"
                    class="flex items-center"
                >
                    <svg
                        class="-ml-1 mr-3 h-5 w-5 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    Processing...
                </span>
            </button>
            @if ($error)
                <div class="mt-4 rounded-lg bg-red-50 p-4 text-red-600">
                    {{ $error }}
                </div>
            @endif

            <div
                x-on:payment-ready.window="
                    const stripe = window.stripe
                    const elements = window.stripeElements

                    if (! stripe || ! elements) {
                        $wire.dispatch('payment-error', {
                            error: 'Payment system not initialized properly',
                        })
                        return
                    }

                    try {
                        const { error: submitError } = await elements.submit()
                        if (submitError) {
                            $wire.dispatch('payment-error', {
                                error: submitError.message,
                            })
                            return
                        }

                        // Get billing details from the form
                        const result = await stripe.confirmPayment({
                            elements,
                            clientSecret: $event.detail.clientSecret,
                            confirmParams: {
                                return_url: '{{ route("payment.success") }}',
                            },
                        })

                        if (result.error) {
                            let errorMessage = result.error.message

                            switch (result.error.type) {
                                case 'card_error':
                                case 'validation_error':
                                    errorMessage = result.error.message
                                    break
                                case 'invalid_request_error':
                                    errorMessage =
                                        'There was a problem with your payment information. Please check and try again.'
                                    break
                                default:
                                    errorMessage = 'An unexpected error occurred. Please try again.'
                            }

                            $wire.dispatch('payment-error', {
                                error: errorMessage,
                            })
                        }
                    } catch (e) {
                        $wire.dispatch('payment-error', {
                            error: 'An unexpected error occurred while processing your payment. Please try again.',
                        })
                    }
                "
            ></div>
        </form>
    </div>
</div>
