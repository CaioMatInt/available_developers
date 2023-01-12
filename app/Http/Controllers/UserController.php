<?php

namespace App\Http\Controllers;

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
        private UserService $authenticationService
    ) { }

    public function login(LoginRequest $request): Response
    {
        return $this->authenticationService->login($request->email, $request->password);
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
        $this->authenticationService->logout($request->user());
        return response()->success();
    }

    public function sendPasswordResetLinkEmail(SendPasswordResetLinkEmailRequest $request): Response
    {
        return $this->authenticationService->sendPasswordResetLinkEmail($request->email);
    }

    public function resetPassword(ResetPasswordRequest $request): Response
    {
        return $this->authenticationService->resetPassword(
            $request->email,
            $request->password,
            $request->password_confirmation,
            $request->token
        );
    }
}
