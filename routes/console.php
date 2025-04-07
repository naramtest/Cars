<?php

Schedule::command("notifications:booking-reminders")->everyTwoMinutes();
Schedule::command("notifications:rent-reminders")->everyTwoMinutes();
Schedule::command("notifications:vehicle-inspections")->everyTwoMinutes();
