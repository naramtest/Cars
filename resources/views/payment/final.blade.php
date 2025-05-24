<x-checkout.pay-layout>
    @if ($payment->isPaid())
        <x-checkout.payment-status.paid :payment="$payment" />
    @elseif ($payment->isRefunded())
        <x-checkout.payment-status.refunded :payment="$payment" />
    @elseif ($payment->isProcessing())
        <x-checkout.payment-status.processing :payment="$payment" />
    @endif
</x-checkout.pay-layout>
