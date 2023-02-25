<?php

namespace App\Services\Authentication;

use App\Repositories\Eloquent\ProviderRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProviderService
{

    public function __construct(
        private UserRepository $userRepository,
        private SocialiteService $socialiteService,
        private ProviderRepository $providerRepository
    ) { }

    public function redirect(string $providerName): RedirectResponse
    {
        return $this->socialiteService->redirect($providerName);
    }

    public function callback(string $providerName): void
    {
        $providerUser = $this->socialiteService->login($providerName);
        $user = $this->userRepository->findByExternalProviderId($providerUser->id);

        if (!$user) {
            $user = $this->userRepository->create([
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'provider_id' => $this->providerRepository->getIdByName($providerName),
                'external_provider_id' => $providerUser->id,
            ]);
        }

        Auth::login($user, true);
    }
}
