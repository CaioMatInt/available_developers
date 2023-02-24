<?php

namespace App\Services\Authentication;

use App\Models\Provider;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProviderService
{
    public array $validProviderNames = [
        Provider::GOOGLE
    ];

    public function __construct(
        private UserService $userService,
        private SocialiteService $socialiteService,
    ) { }

    public function redirect(string $providerName): RedirectResponse
    {
        return $this->socialiteService->redirect($providerName);
    }

    public function callback(string $providerName): void
    {
        $providerUser = $this->socialiteService->login($providerName);
        $user = $this->userService->findOrCreate($providerUser, $providerName);

        Auth::login($user, true);
    }

}
