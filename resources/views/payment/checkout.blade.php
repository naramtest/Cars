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

                <livewire:stripe-payment-component :payment="$payment" />

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

    @pushonce("scripts")
        <script src="https://js.stripe.com/v3/"></script>
    @endpushonce
</x-app-layout>
