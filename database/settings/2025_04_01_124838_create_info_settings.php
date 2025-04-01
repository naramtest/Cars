<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add("info.name", ["en" => ""]);
        $this->migrator->add("info.address", ["en" => ""]);
        $this->migrator->add("info.about", ["en" => ""]);
        $this->migrator->add("info.slogan", ["en" => ""]);
        $this->migrator->add("info.phones", []);
        $this->migrator->add("info.emails", []);
        $this->migrator->add("info.socials", []);
    }
};
