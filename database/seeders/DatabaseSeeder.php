<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            CategorySeeder::class,
            RoleSeeder::class,
            SettingSeeder::class,
            SiteSettingSeeder::class,
            SubscriptionPlanSeeder::class,
            ContentSeeder::class,
            FeedDemoSeeder::class,
        ]);
    }
}