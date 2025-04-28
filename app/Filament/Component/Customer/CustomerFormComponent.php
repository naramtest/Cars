<?php

namespace App\Filament\Component\Customer;

use App\Filament\Resources\CustomerResource;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CustomerFormComponent
{
    public static function clientInformationSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    // TODO: show phone number also not just the customer name in select
                    Forms\Components\Select::make("customer")
                        ->label(__("dashboard.Customer"))
                        ->relationship("customer", "name")
                        ->preload()
                        ->searchable(["name", "email", "phone_number"])
                        ->createOptionForm([
                            Forms\Components\TextInput::make("name")
                                ->label(__("dashboard.name"))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make("email")
                                ->label(__("dashboard.email"))
                                ->email()
                                ->maxLength(255),
                            PhoneInput::make("phone_number")
                                ->label(__("dashboard.phone_number"))
                                ->initialCountry("AE")
                                ->required(),
                            Forms\Components\Textarea::make("notes")
                                ->label(__("dashboard.notes"))
                                ->maxLength(1000),
                        ])
                        ->required()
                        ->helperText(__("dashboard.Select or Create Customer")),

                    // Display the selected customers if in edit mode
                    Forms\Components\Placeholder::make("customer_list")
                        ->label(__("dashboard.selected_customers"))
                        ->content(function (
                            callable $get,
                            callable $set,
                            ?Model $record
                        ) {
                            if (!$record) {
                                return "";
                            }

                            $customerList = "";
                            // Generate URL to customer view page
                            $url = CustomerResource::getUrl("view", [
                                "record" => $record->customer,
                            ]);

                            // Create a clickable link to the customer view page
                            $customerList .= sprintf(
                                '• <a href="%s" target="_blank" class="text-primary-600 hover:underline">%s</a> (%s)<br>',
                                $url,
                                e($record->customer->name),
                                e($record->customer->phone_number)
                            );

                            return new HtmlString($customerList);
                        })
                        ->visible(
                            fn(string $operation): bool => $operation === "edit"
                        ),
                ])
                ->columns(1)
                ->columnSpan(1),
        ];
    }
}
