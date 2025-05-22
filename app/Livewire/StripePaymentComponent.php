<?php

namespace App\Livewire;

use App\Enums\Payments\PaymentStatus;
use App\Enums\Payments\PaymentType;
use App\Models\Payment;
use App\Services\Payments\PaymentManager;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class StripePaymentComponent extends Component
{
    public Payment $payment;
    public bool $processing = false;
    public ?string $error = null;

    public function mount(Payment $payment): void
    {
        $this->payment = $payment;
    }

    public function render()
    {
        return view("livewire.stripe-payment-component");
    }

    #[On("payment-error")]
    public function setPaymentError($error): void
    {
        $this->error = $error;
        $this->processing = false;
    }

    public function pay()
    {
        try {
            $this->processing = true;
            $this->error = null;
            if ($this->payment->status === PaymentStatus::PAID) {
                $this->error = "This payment already processed";
            }

            $payment = app(PaymentManager::class)
                ->driver(PaymentType::STRIPE_ELEMENTS)
                ->pay($this->payment);
            $this->dispatch(
                "payment-ready",
                clientSecret: $payment->metadata["client_secret"]
            );
        } catch (Exception) {
            $this->processing = false;

            DB::rollBack();
            $this->error = __("store.Failed to create order. Please try again");
        }
    }
}
