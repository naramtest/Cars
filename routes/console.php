<?php

if (App::isProduction()) {
    Schedule::command("model:prune")->daily();
    Schedule::command("queue:work --stop-when-empty")
        ->everyMinute()
        ->withoutOverlapping();
    Schedule::command("notifications:booking-reminders")->everyTwoMinutes();
    Schedule::command("notifications:rent-reminders")->everyTwoMinutes();
    Schedule::command("notifications:shipping-reminders")->everyTwoMinutes();
    Schedule::command("notifications:vehicle-inspections")->daily();
    Schedule::command("notifications:vehicle-registrations")->daily();

    //Driver
    Schedule::command("notifications:driver-vehicle-inspections")->daily();
    Schedule::command(
        "notifications:shipping-driver-reminders"
    )->everyTwoMinutes();
}
