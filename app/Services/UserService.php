<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) { }

    public function login(string $email, string $password): void
    {
        $authAttemptWasSuccessful = Auth::attempt(['email' => $email, 'password' => $password]);

        if (!$authAttemptWasSuccessful) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }
    }

    public function createUserToken(): string
    {
        return auth()->user()->createToken('LaravelSanctumAuth')->plainTextToken;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function sendPasswordResetLinkEmail(string $email): Response
    {
        $status = Password::sendResetLink(
            ['email' => $email],
            function () {
                route('user.password.reset');
            }
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->success(200, [], __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status)
        ]);
    }

    public function resetPassword(string $email, string $password, string $passwordConfirmation, string $token)
    {
        $status = Password::reset([
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
            'token' => $token
        ], function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->success(200, [], __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status)
        ]);
    }

    /**
     * @throws \Exception
     */
    public function checkIfHasRegisteredWithAnotherProvider(string $userEmail, string $providerName): void
    {
        $user = $this->userRepository->findByEmailWithProvider($userEmail);

        if ($user && $user->provider->name !== $providerName) {
            /** @@TODO: Implement specific Exception */
            throw new \Exception("You tried signing in as {$userEmail} via {$providerName},
                which is not the authentication method you used during sign up.
                 Try again using the authentication method you used during sign up.");
        }
    }
}
