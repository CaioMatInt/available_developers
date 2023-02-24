<?php

namespace Database\Seeders;

use App\Enum\ProfileTypeEnum;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrator = Administrator::factory()->create();
        try {
            User::factory()->create([
                'name' => 'General Administrator',
                'email' => 'generaladmin@admin.com',
                //@@TODO: Remove before realeasing
                'password' => bcrypt('password'),
                'profile_type' => ProfileTypeEnum::Administrator->value,
                'profile_id' => $administrator->id,
            ]);
        }catch (\Exception $exception){
            dd($exception);
        }
    }
}
