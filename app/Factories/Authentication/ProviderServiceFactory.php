<?php

namespace App\Factories\Authentication;

use App\Services\Authentication\GoogleProviderService;

class ProviderServiceFactory
{
    public array $providerServices = [
        'google' => GoogleProviderService::class
    ];

    public function handle(string $provider): object
    {
        if (!array_key_exists($provider, $this->providerServices)) {
            //**@@TODO: return 404 response
            throw new \Exception('Authentication provider not found');
        }

        $service = $this->providerServices[$provider];
        return app($service);
    }
}
