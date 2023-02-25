<?php

namespace Service;

use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Helper\UserTrait;
use Tests\TestCase;


class UserServiceTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;
    private UserService $userService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
        $this->mockVariables();
    }

    private function mockVariables()
    {
        $this->mockAdministrator();
    }

    /** @test */
    public function a_valid_and_verified_user_can_login_with_correct_email_and_password()
    {
        $this->userService->login($this->administrator->email, $this->unhashedUserPassword);

        $this->assertTrue(Auth::check());
    }

    /** @test */
    public function can_create_user_token()
    {
        $this->actingAs($this->administrator);
        $token = $this->userService->createUserToken();

        $this->assertTrue(strlen($token) === 42);
    }
}

