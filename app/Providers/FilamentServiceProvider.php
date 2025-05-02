<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->defaultSort("created_at", "desc");
        });
        Filament::serving(function () {
            Field::macro("translate", function (bool $isInline = false) {
                if ($isInline) {
                    $this->helperText(
                        view("filament.custom-helper-text", [
                            "icon" => "gmdi-translate-o",
                            "text" => __("dashboard.Translatable"),
                        ])
                    );
                } else {
                    $this->hint(__("dashboard.Translatable"));
                    $this->hintIcon("gmdi-translate-o");
                }

                return $this;
            });
            Field::macro("counter", function ($field, $max) {
                $this->hint(
                    view("filament.char-counter", [
                        "field" => $field,
                        "max" => $max,
                    ])
                );

                return $this;
            });
            TextColumn::macro("withTooltip", function () {
                return $this->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    return $state;
                });
            });
        });
    }
}
