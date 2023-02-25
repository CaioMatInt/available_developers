<?php

namespace Tests\Helper;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait ExternalProviderTrait
{
    private object $googleResponse;

    private function mockGoogleResponse(): void
    {
        $googleResponse = file_get_contents(base_path
            ('tests/Mocks/Authentication/google_provider_authentication_response.json')
        );

        $this->googleResponse = json_decode($googleResponse);
    }

}
