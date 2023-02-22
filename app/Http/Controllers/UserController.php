<?php

namespace App\Http\Controllers;

use App\Enum\ProviderEnum;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RedirectToLoginWithProviderRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\SendPasswordResetLinkEmailRequest;
use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserResource;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Authentication\ProviderService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private ProviderService $providerService,
    ) { }

    public function login(LoginRequest $request): Response
    {
        $this->userService->login($request->email, $request->password);
        $userToken = $this->userService->createUserToken();
        return response(UserLoginResource::make([
            'user' => auth()->user(),
            'token' => $userToken
        ]));
    }

    public function redirectToLoginWithProvider(RedirectToLoginWithProviderRequest $request): RedirectResponse
    {
        return $this->providerService->redirect($request->route('provider_name'));
    }

    public function loginCallbackOfProvider(Request $request): Response
    {
        $this->providerService->callback($request->route('provider_name'));
        $userToken = $this->userService->createUserToken();
        return response(UserLoginResource::make([
            'user' => auth()->user(),
            'token' => $userToken
        ]));
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
