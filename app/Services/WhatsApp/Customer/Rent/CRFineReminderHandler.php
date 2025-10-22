<?php

namespace App\Services\WhatsApp\Customer\Rent;

use App\Models\Fine;
use App\Models\Rent;
use App\Services\Currency\CurrencyService;
use App\Services\WhatsApp\Abstract\WhatsAppAbstractHandler;

class CRFineReminderHandler extends WhatsAppAbstractHandler
{
    /**
     * Prepares body data for customer traffic fine reminder notification
     *
     * @param Rent $modelData
     * @return array
     */
    public function prepareBodyData($modelData): array
    {
        $customer = $modelData->getCustomer();

        // Get all pending fines for this customer's rents
        $allPendingFines = Fine::whereHas('rent', function ($query) use ($customer) {
            $query->whereHas('customer', function ($q) use ($customer) {
                $q->where('customers.id', $customer->id);
            });
        })->pending()->get();

        // Format fine details
        $fineDetails = $allPendingFines->map(function ($fine) {
            return "• $fine->name = $fine->formatted_amount";
        })->join("\n\n");

        // Calculate total amount
        $totalAmount = $allPendingFines->sum('amount');
        $currencyService = app(CurrencyService::class);
        $totalFormatted = $currencyService->format(
            $currencyService->money($totalAmount, 'AED')
        );

        return $this->formatBodyParameters([
            $customer->name, // 1 - Customer name
            $fineDetails, // 2 - Fine details list
            $totalFormatted, // 3 - Total amount
        ]);
    }

    public function prepareButtonData($modelData): array
    {
        return []; // No buttons for this notification
    }

    public function facebookTemplateData(): array
    {
        return [
            "name" => $this->getTemplateName(),
            "language" => "en_US",
            "category" => "UTILITY",
            "components" => [
                [
                    "type" => "BODY",
                    "text" =>
                        "Dear {{1}},\n\n" .
                        "This is a friendly reminder from XYZ Rent A Car L.L.C to settle the traffic fines issued under your rented vehicle to avoid any additional penalties or renewal delays.\n\n" .
                        "📋 Fine Details:\n" .
                        "{{2}}\n\n" .
                        "📌 Total Due = {{3}} only.\n\n" .
                        "Payment can be made via bank transfer to the company account below or by visiting our main office in Al Quoz.\n\n" .
                        "For any inquiries or payment confirmation, please contact us directly through this number.\n\n" .
                        "Thank you for your cooperation 🌟\n\n" .
                        "🏦 Bank Details:\n" .
                        "Bank: Mashreq Bank\n" .
                        "Account Name: XYZ Rent A Car L.L.C\n" .
                        "IBAN: AE47 0304 0000 1234 5678 901\n" .
                        "Swift Code: BOMLAEAD\n" .
                        "Branch: Al Quoz – Dubai",
                    "example" => [
                        "body_text" => [
                            [
                                "Mr. Simon", // {{1}} Customer name
                                "• First Fine (Speed) = AED 620\n• Second Fine (Sharjah / Speed) = AED 260", // {{2}} Fine details
                                "AED 880", // {{3}} Total amount
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the group this handler belongs to
     */
    public function getGroup(): string
    {
        return "customer";
    }

    /**
     * Get phone numbers to send the notification to
     */
    public function phoneNumbers($data)
    {
        return $data->getCustomer()->phone_number;
    }

    /**
     * Get the reminder timing in minutes (24 hours)
     */
    public function getReminderTiming(): int
    {
        return 1440; // 24 hours = 1440 minutes
    }
}
