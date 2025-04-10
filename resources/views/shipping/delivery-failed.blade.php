<x-app-layout>
    <main class="flex min-h-screen items-center justify-center bg-gray-100">
        <div
            class="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-md"
        >
            <div class="mb-4 text-red-500">
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
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
            </div>
            <h1 class="mb-4 text-2xl font-bold">Confirmation Failed</h1>
            <p class="mb-6">{{ $message }}</p>
            <p class="text-sm text-gray-500">
                Please contact support if you need assistance.
            </p>
        </div>
    </main>
</x-app-layout>
