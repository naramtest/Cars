<x-app-layout>
    <main class="flex min-h-screen items-center justify-center bg-gray-100">
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-md">
            <h1 class="mb-4 text-center text-2xl font-bold">
                Confirm Delivery
            </h1>
            <div class="mb-6">
                <p class="mb-2 text-gray-700">
                    Shipping:
                    <strong>{{ $shipping->reference_number }}</strong>
                </p>
                <p class="mb-2 text-gray-700">
                    Client:
                    <strong>{{ $shipping->client_name }}</strong>
                </p>
                <p class="mb-2 text-gray-700">
                    Delivery Address:
                    <strong>{{ $shipping->delivery_address }}</strong>
                </p>
            </div>

            <form
                action="{{ route("shipping.driver.process-delivery") }}"
                method="POST"
            >
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />

                <div class="mb-4">
                    <label
                        for="delivery_notes"
                        class="mb-2 block font-medium text-gray-700"
                    >
                        Delivery Notes (optional)
                    </label>
                    <textarea
                        id="delivery_notes"
                        name="delivery_notes"
                        rows="4"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Add any notes about the delivery (e.g., who received the package, where it was left)"
                    ></textarea>
                </div>

                <div class="flex justify-center">
                    <button
                        type="submit"
                        class="rounded-md bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        Confirm Delivery
                    </button>
                </div>
            </form>
        </div>
    </main>
</x-app-layout>
