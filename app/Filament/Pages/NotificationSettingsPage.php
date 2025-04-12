<?php

namespace App\Filament\Pages;

use App\Settings\NotificationSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class NotificationSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = "heroicon-o-bell-alert";

    protected static ?string $navigationLabel = "Notification Settings";
    protected static string $view = "filament.pages.notification-settings";
    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __("dashboard.System Settings");
    }

    public function mount(NotificationSettings $settings): void
    {
        $this->form->fill([
            "enabled_templates" => $settings->enabled_templates,
            "reminder_timings" => $settings->reminder_timings,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make("Notification Settings")
                    ->tabs([
                        $this->getAdminTab(),
                        $this->getDriverTab(),
                        $this->getCustomerTab(),
                    ])
                    ->columnSpanFull()
                    ->contained(false),
            ])
            ->statePath("data");
    }

    private function getAdminTab(): Tab
    {
        return Tab::make("Admin Notifications")
            ->icon("heroicon-o-user")
            ->schema([$this->getTemplateTogglesForGroup("admin")]);
    }

    private function getTemplateTogglesForGroup(string $group): Section
    {
        $templates = config("notification_templates.$group", []);
        $settings = app(NotificationSettings::class);
        $descriptions = $settings->template_descriptions;

        $schema = [];

        foreach ($templates as $name => $class) {
            // Check if the template is a reminder type that needs a timing setting
            $isReminderType = Str::contains($name, ["reminder"]);

            $schema[] = Grid::make(3)->schema([
                Toggle::make("enabled_templates")
                    ->label(Str::headline($name))
                    ->helperText(
                        $descriptions[$name] ?? "No description available"
                    )
                    ->columnSpan(2)
                    ->default(in_array($name, $settings->enabled_templates))
                    ->inline(false)
                    ->offIcon("heroicon-m-x-mark")
                    ->onIcon("heroicon-m-check")
                    ->onColor("success")
                    ->reactive()
                    ->afterStateHydrated(function ($component, $state) use (
                        $name,
                        $settings
                    ) {
                        // This code transforms the array of enabled templates into individual toggle states
                        $component->state(
                            in_array($name, $settings->enabled_templates)
                        );
                    })
                    ->dehydrated(false),

                // If it's a reminder type, add a timing input
                $isReminderType
                    ? Select::make("reminder_timings.$name")
                        ->label("Timing")
                        ->options([
                            15 => "15 minutes before",
                            30 => "30 minutes before",
                            60 => "1 hour before",
                            120 => "2 hours before",
                            180 => "3 hours before",
                            360 => "6 hours before",
                            720 => "12 hours before",
                            1440 => "1 day before",
                            2880 => "2 days before",
                            4320 => "3 days before",
                            7200 => "5 days before",
                            10080 => "1 week before",
                        ])
                        ->default($settings->reminder_timings[$name] ?? 120)
                        ->visible(
                            fn(callable $get) => in_array(
                                $name,
                                $settings->enabled_templates
                            )
                        )
                        ->columnSpan(1)
                    : Placeholder::make("")->content("")->columnSpan(1),
            ]);
        }

        return Section::make(Str::headline($group) . " Notifications")
            ->description(
                "Control which notifications are sent to {$group}s and when"
            )
            ->schema($schema)
            ->collapsible();
    }

    private function getDriverTab(): Tab
    {
        return Tab::make("Driver Notifications")
            ->icon("heroicon-o-truck")
            ->schema([$this->getTemplateTogglesForGroup("driver")]);
    }

    private function getCustomerTab(): Tab
    {
        return Tab::make("Customer Notifications")
            ->icon("heroicon-o-users")
            ->schema([$this->getTemplateTogglesForGroup("customer")]);
    }

    public function save(): void
    {
        $settings = app(NotificationSettings::class);
        $formData = $this->form->getState();

        // Process enabled_templates
        $enabledTemplates = [];
        $templateGroups = config("notification_templates", []);

        foreach ($templateGroups as $group => $templates) {
            foreach ($templates as $name => $class) {
                $isEnabled = $this->getToggleState($name);
                if ($isEnabled) {
                    $enabledTemplates[] = $name;
                }
            }
        }

        // Save settings
        $settings->enabled_templates = $enabledTemplates;
        $settings->reminder_timings = $formData["reminder_timings"] ?? [];
        $settings->save();

        Notification::make()
            ->title("Settings saved successfully")
            ->success()
            ->send();
    }

    private function getToggleState(string $templateName): bool
    {
        $formName = "enabled_templates";

        // Check if this specific toggle exists in the request
        if (
            request()->has("data.$formName") &&
            is_array(request("data.$formName"))
        ) {
            return in_array($templateName, request("data.$formName"));
        }

        // Default to the current setting
        $settings = app(NotificationSettings::class);
        return in_array($templateName, $settings->enabled_templates);
    }
}
