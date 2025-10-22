<?php

namespace App\Filament\Resources\RentResource\RelationManagers;

use App\Enums\FineStatus;
use App\Services\WhatsApp\Customer\Rent\CRFineReminderHandler;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class FinesRelationManager extends RelationManager
{
    protected static string $relationship = 'fines';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Fine Name')
                    ->required()
                    ->placeholder('e.g., Speed Fine, Parking Fine')
                    ->maxLength(255),

                MoneyInput::make('amount')
                    ->label('Fine Amount')
                    ->currency('AED')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(FineStatus::class)
                    ->default(FineStatus::Pending)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Fine Name')
                    ->searchable()
                    ->sortable(),

                MoneyColumn::make('amount')
                    ->label('Amount')
                    ->currency('AED')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                ,

                Tables\Columns\TextColumn::make('last_notification_sent_at')
                    ->label('Last Notification')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('Never sent'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(FineStatus::class),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Fine'),


                Tables\Actions\Action::make('send_fine_notification')
                    ->label('Send Notification')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(function () {
                        return $this->getOwnerRecord()->fines()->pending()->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Fine Notification')
                    ->modalDescription('This will send a WhatsApp notification to the customer about their pending fines.')
                    ->action(function () {
                        $success = false;
                        try {
                            $rent = $this->getOwnerRecord();
                            $handler = app(CRFineReminderHandler::class);
                            $whatsAppService = app(WhatsAppNotificationService::class);
                            $result = $whatsAppService->send($handler, $rent);
                            if ($result) {
                                $rent->fines()->pending()->get()->each(function ($fine) {
                                    $fine->updateLastNotificationSent();
                                });
                                $success = true;
                            }
                        } catch (\Exception $e) {

                        }

                        if ($success) {
                            return Notification::make()
                                ->title('Notification Sent')
                                ->success()
                                ->body('Fine reminder notification has been sent to the customer.')
                                ->send();
                        }

                        return Notification::make()
                            ->title('Notification Failed')
                            ->danger()
                            ->body('Failed to send notification: ' . $e->getMessage())
                            ->send();

                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('mark_as_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === FineStatus::Pending)
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['status' => FineStatus::Paid])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('mark_as_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['status' => FineStatus::Paid]))),
                ]),
            ]);
    }
}
