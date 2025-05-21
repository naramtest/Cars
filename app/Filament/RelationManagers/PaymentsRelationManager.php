<?php

namespace App\Filament\RelationManagers;

use App\Enums\Payments\PaymentStatus;
use App\Enums\Payments\PaymentType;
use App\Filament\Component\DateColumn;
use App\Models\Payment;
use App\Traits\HasPaymentActions;
use Exception;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class PaymentsRelationManager extends RelationManager
{
    use HasPaymentActions;

    protected static string $relationship = "payments";

    protected static ?string $recordTitleAttribute = "id";

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                MoneyColumn::make("amount")
                    ->label(__("dashboard.amount"))
                    ->sortable(),

                Tables\Columns\TextColumn::make("payment_method")->label(
                    "Method"
                ),
                Tables\Columns\TextColumn::make("status")
                    ->label(__("dashboard.status"))
                    ->badge(),
                DateColumn::make(
                    "created_at",
                    __("dashboard.created_at"),
                    false
                ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("status")->options(
                    PaymentStatus::class
                ),
            ])
            ->headerActions([
                Tables\Actions\Action::make("paymentOptions")
                    ->label("Create Payment Link")
                    ->icon("heroicon-o-currency-dollar")
                    ->color("success")
                    ->form([
                        Toggle::make("sendLink")
                            ->label("Send Payment Link To Customer")
                            ->inline()
                            ->default(true),
                        MoneyInput::make("amount")
                            ->required()
                            ->label(__("dashboard.amount"))
                            ->default(function (RelationManager $livewire) {
                                $record = $livewire->getOwnerRecord();
                                return $record->total_price ?? 0;
                            })
                            ->helperText(function () {
                                return new HtmlString(
                                    '<span class="text-danger-600 dark:text-danger-400">' .
                                        __("dashboard.amount_payment_note") .
                                        "</span>"
                                );
                            }),
                        Textarea::make("note")
                            ->label("Payment Note")
                            ->rows(2)
                            ->placeholder(
                                "Enter a note about this payment (optional)"
                            )
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $record = $livewire->getOwnerRecord();
                        if ($data["sendLink"]) {
                            $this->generateAndSend($record, $data);
                        } else {
                            $this->generate($record, $data);
                        }
                    })
                    ->modalWidth("lg"),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->mutateRecordDataUsing(
                    function (array $data): array {
                        if ($data["status"] === PaymentStatus::PAID->value) {
                            $data["paid_at"] = now()->toIso8601String();
                            $data["metadata"] = [
                                "manually_marked_as_paid" => true,
                            ];
                        }
                        return $data;
                    }
                ),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make("sendPaymentLink")
                        ->label("Send Link")
                        ->icon("heroicon-o-paper-airplane")
                        ->color("primary")
                        ->action(function (Payment $record) {
                            $this->send($record);
                        })
                        ->visible(
                            fn(Payment $record) => !empty($record->payment_link)
                        ),
                    Tables\Actions\Action::make("markAsPaid")
                        ->label("Mark as Paid")
                        ->icon("heroicon-o-check-circle")
                        ->color("success")
                        ->action(function (Payment $record) {
                            $record->updatePaymentToPaid([
                                "manually_marked_as_paid" => true,
                            ]);
                            Notification::make()
                                ->title("Payment marked as paid")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(
                            fn(Payment $record) => $record->status ===
                                PaymentStatus::PENDING or
                                $record->status === PaymentStatus::PROCESSING
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->latest());
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make("status")
                ->label("Payment Status")
                ->options(PaymentStatus::class)
                ->required(),
            MoneyInput::make("amount")->disabled(function ($operation) {
                return $operation === "edit";
            }),
            DateTimePicker::make("paid_at"),
            Select::make("payment_method")->options(PaymentType::class),
            TextInput::make("provider_id")
                ->label("Payment ID / Reference")
                ->columnSpanFull(),
            Textarea::make("note")->label("Note")->columnSpanFull(),
            KeyValue::make("metadata")
                ->label("Payment Additional information")
                ->columnSpanFull(),
        ]);
    }
}
