<?php

namespace App\Services\Authentication;

use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProviderService
{
    public array $validProviderNames = [
        'google',
        'facebook',
        'linkedin',
        'github',
    ];

    public function __construct(
        private UserRepository $userRepository,
    ) { }

    public function redirect(string $provider): RedirectResponse
    {
        //@@TODO: remove this validation and create a Request
        if (!in_array($provider, $this->validProviderNames)) {
            throw new \Exception('Invalid provider name');
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider): void
    {
        $providerUser = Socialite::driver($provider)->stateless()->user();
        $user = $this->userRepository->findByProviderId($providerUser->getId());

        if (!$user) {
            $user = $this->userRepository->create([
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'provider' => $provider,
                'provider_id' => $providerUser->id,
            ]);
        }

        Auth::login($user, true);
    }
}
