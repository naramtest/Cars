<?php

namespace App\Filament\Actions\Type;

use App\Enums\TypesEnum;
use App\Models\Type;
use Auth;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

class TypeActions
{
    public static function actions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()->hidden(function (Type $record) {
                return !Auth::user()->can("delete", $record) or
                    !TypesEnum::getExists($record);
            }),

            //            self::moveAndDeleteAction(),
        ];
    }

    protected static function moveAndDeleteAction()
    {
        return Action::make("Delete")
            ->label(__("dashboard.Delete"))
            ->form([
                Select::make("types")
                    ->options(function (Type $record) {
                        return Type::select(["name", "id", "slug"])
                            ->whereNot("id", $record->id)
                            ->where("type", $record->type)
                            ->pluck("name", "id");
                    })
                    ->default("general")
                    ->required()
                    ->hiddenLabel(),
            ])
            ->color("danger")
            ->modalWidth("md")
            ->icon("heroicon-m-trash")
            ->modalIcon("heroicon-o-trash")
            ->modalAlignment(Alignment::Center)
            ->modalHeading(__("dashboard.Delete Category"))
            ->modalDescription(
                __(
                    "dashboard.This Category Has Items. Please Select a Category From the Dropdown to Move Them."
                )
            )
            ->action(function (array $data, Type $record): void {
                if ($record->type === CategoryType::PRODUCT) {
                    $products = Product::whereHas(
                        "categories",
                        fn($query) => $query->where("category_id", $record->id)
                    )->get();
                    // TODO: check if the attach works
                    $products->each(
                        fn($product) => $product
                            ->categories()
                            ->attach($data["category"])
                    );
                } elseif ($record->type === CategoryType::POST) {
                    $posts = Post::whereHas(
                        "categories",
                        fn($query) => $query->where("category_id", $record->id)
                    )->get();
                    $posts->each(
                        fn($post) => $post
                            ->categories()
                            ->attach($data["category"])
                    );
                }

                if ($record->delete()) {
                    Notification::make()
                        ->success()
                        ->title(
                            __(
                                "filament-actions::delete.single.notifications.deleted.title"
                            )
                        )
                        ->send();
                }
            })
            ->modalSubmitActionLabel(__("dashboard.Move & Delete"))
            ->hidden(function (Category $record) {
                $exists =
                    $record->type === CategoryType::PRODUCT
                        ? $record->products()->exists()
                        : $record->posts()->exists();
                return !Auth::user()->can("delete", $record) or !$exists;
            });
    }
}
