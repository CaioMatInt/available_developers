<?php

namespace Database\Seeders;

use App\Models\SocialNetwork;
use Illuminate\Database\Seeder;

class SocialNetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $socialNetworks = [
            [
                "name" => "LinkedIn",
                "link" => "https://www.linkedin.com",
                "logo" => "https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png"
            ],
            [
                "name" => "GitHub", "link" => "https://www.github.com",
                "logo" => "https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png"
            ],
            [
                "name" => "Facebook", "link" => "https://www.facebook.com",
                "logo" => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMvz62APrx07pMHBeiqz2NKdvvNFb3WOKXSA&usqp=CAU"
            ],
            [
                "name" => "YouTube", "link" => "https://www.youtube.com",
                "logo" => "https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/YouTube_Logo_2017.svg/1200px-YouTube_Logo_2017.svg.png"
            ],
            [
                "name" => "Instagram", "link" => "https://www.instagram.com",
                "logo" => "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/1200px-Instagram_logo_2016.svg.png"
            ],
            [
                "name" => "Twitter", "link" => "https://www.twitter.com",
                "logo" => "https://cdn-icons-png.flaticon.com/512/124/124021.png"
            ],
        ];

        foreach ($socialNetworks as $socialNetwork) {
            SocialNetwork::insert([
                'name' => $socialNetwork['name'],
                'link' => $socialNetwork['link'],
                'logo' => $socialNetwork['logo'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
