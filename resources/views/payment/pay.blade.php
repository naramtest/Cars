<x-checkout.pay-layout>
    <livewire:stripe-payment-component :payment="$payment" />

    @pushonce("scripts")
        <script src="https://js.stripe.com/v3/"></script>
    @endpushonce
</x-checkout.pay-layout>
