<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SurahSeeder::class,
            JuzSeeder::class,
            AdminSeeder::class,
            SubscriptionPlanSeeder::class,
            ModulePriceSeeder::class,
            NotificationTemplateSeeder::class,
        ]);
    }
}