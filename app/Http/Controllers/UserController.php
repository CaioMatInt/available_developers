<?php

namespace App\Http\Controllers;

use App\Factories\Authentication\ProviderServiceFactory;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RegisterRequest;
use App\Http\Requests\Authentication\ResetPasswordRequest;
use App\Http\Requests\Authentication\SendPasswordResetLinkEmailRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Eloquent\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private ProviderServiceFactory $providerServiceFactory
    ) { }

    public function login(LoginRequest $request): Response
    {
        return $this->userService->login($request->email, $request->password);
    }

    public function loginWithProvider(Request $request)
    {
        $provider = 'google';
        $providerService = $this->providerServiceFactory->handle($provider);
        return $this->userService->loginWithExternalProvider($request->all());
    }

    public function register(RegisterRequest $request): Response
    {
        $data = $request->only('name', 'email', 'password', 'profile_type');
        $data['password'] = bcrypt($data['password']);
        $this->userRepository->create($data);
        return response()->success(Response::HTTP_CREATED);
    }

    public function getAuthenticatedUser(): Response
    {
        $userResource = new UserResource($this->userRepository->getAuthenticatedUser());
        return response($userResource);
    }

    public function logout(Request $request): Response
    {
        $this->userService->logout($request->user());
        return response()->success();
    }

    public function sendPasswordResetLinkEmail(SendPasswordResetLinkEmailRequest $request): Response
    {
        return $this->userService->sendPasswordResetLinkEmail($request->email);
    }

    public function resetPassword(ResetPasswordRequest $request): Response
    {
        return $this->userService->resetPassword(
            $request->email,
            $request->password,
            $request->password_confirmation,
            $request->token
        );
    }
}
