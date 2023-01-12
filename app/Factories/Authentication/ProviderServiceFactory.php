<?php

namespace App\Factories\Authentication;

use App\Services\Authentication\GoogleProviderAuthenticationService;

class ProviderServiceFactory
{
    public array $providerServices = [
        'google' => GoogleProviderAuthenticationService::class
    ];

    public function handle(string $provider): object
    {
        $service = $this->providerServices[$provider];
        return app($service);
    }
}
