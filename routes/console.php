<?php

if (App::isProduction()) {
    Schedule::command("model:prune")->daily();
    Schedule::command("queue:work --stop-when-empty")
        ->everyMinute()
        ->withoutOverlapping();
    Schedule::command("notifications:booking-reminders")->everyTwoMinutes();
    Schedule::command("notifications:rent-reminders")->everyTwoMinutes();
    Schedule::command("notifications:shipping-reminders")->everyTwoMinutes();

    // Update vehicle inspection dates early in the morning
    Schedule::command("vehicles:update-inspection-dates")->dailyAt("07:00");

    // Send admin notifications at the start of the business day
    Schedule::command("notifications:vehicle-inspections")->dailyAt("09:00");
    Schedule::command("notifications:vehicle-registrations")->dailyAt("09:15");

    // Send driver notifications slightly later to stagger them
    Schedule::command("notifications:driver-vehicle-inspections")->dailyAt(
        "09:30"
    );
    Schedule::command(
        "notifications:shipping-driver-reminders"
    )->everyTwoMinutes();
}
