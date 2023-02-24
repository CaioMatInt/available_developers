<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProviderSeeder::class,
            AdministratorSeeder::class,
            CountrySeeder::class,
            DeveloperTypeSeeder::class,
            LanguageSeeder::class,
            SocialNetworkSeeder::class,
            TechSkillSeeder::class
        ]);
    }
}
