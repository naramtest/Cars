<?php

namespace App\Filament\Actions\Reservation;

use App\Enums\ReservationStatus;
use App\Models\Model;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class ReservationActions
{
    public static function make()
    {
        return ActionGroup::make([
            Action::make("complete")
                ->label(__("dashboard.mark_as_completed"))
                ->icon("heroicon-o-check-circle")
                ->color("success")
                ->action(function (
                    \Illuminate\Database\Eloquent\Model $record
                ) {
                    $record->update([
                        "status" => ReservationStatus::Completed->value,
                    ]);
                })
                ->requiresConfirmation()
                ->visible(
                    fn(
                        \Illuminate\Database\Eloquent\Model $record
                    ) => $record->status == ReservationStatus::Active or
                        $record->status == ReservationStatus::Confirmed
                ),

            Action::make("activate")
                ->label(__("dashboard.mark_as_active"))
                ->icon("heroicon-o-play")
                ->color("warning")
                ->action(function (
                    \Illuminate\Database\Eloquent\Model $record
                ) {
                    $record->update([
                        "status" => ReservationStatus::Active->value,
                    ]);
                })
                ->requiresConfirmation()
                ->visible(
                    fn(
                        \Illuminate\Database\Eloquent\Model $record
                    ) => $record->status == ReservationStatus::Confirmed ||
                        $record->status == ReservationStatus::Pending
                ),

            Action::make("confirm")
                ->label(__("dashboard.confirm_rent"))
                ->icon("heroicon-o-check")
                ->color("primary")
                ->action(function (
                    \Illuminate\Database\Eloquent\Model $record
                ) {
                    $record->update([
                        "status" => ReservationStatus::Confirmed->value,
                    ]);
                })
                ->requiresConfirmation()
                ->visible(
                    fn(
                        \Illuminate\Database\Eloquent\Model $record
                    ) => $record->status == ReservationStatus::Cancelled ||
                        $record->status == ReservationStatus::Pending
                ),

            Action::make("cancel")
                ->label(__("dashboard.cancel_rent"))
                ->icon("heroicon-o-x-circle")
                ->color("danger")
                ->action(function (
                    \Illuminate\Database\Eloquent\Model $record
                ) {
                    $record->update([
                        "status" => ReservationStatus::Cancelled->value,
                    ]);
                })
                ->requiresConfirmation()
                ->visible(
                    fn(
                        \Illuminate\Database\Eloquent\Model $record
                    ) => $record->status != ReservationStatus::Completed &&
                        $record->status != ReservationStatus::Cancelled
                ),
        ]);
    }
}
