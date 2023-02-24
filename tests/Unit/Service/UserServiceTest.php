<?php

namespace Service;

use App\Models\Provider;
use App\Models\User;
use App\Repositories\Eloquent\ProviderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;


class UserServiceTest extends TestCase
{
    use RefreshDatabase;
    private UserService $userService;
    private User $administrator;
    private string $unhashedUserPassword = '123456';

    public function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
        $this->mockVariables();
    }

    private function mockUsers(): void
    {
        $this->administrator = User::factory()->create([
            'password' => Hash::make($this->unhashedUserPassword)
        ]);
    }

    private function mockVariables()
    {
        $this->mockUsers();
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

    /** @test */
    public function can_find_user_when_logging_with_provider()
    {
        $user = User::factory()->create([
            'external_provider_id' => '1234567890'
        ]);

        $userRepositoryStub = $this->createStub(UserRepository::class);
        $userRepositoryStub->method('findByExternalProviderId')
            ->willReturn($user);

        $userService = new UserService($userRepositoryStub, app(ProviderRepository::class));
        $userFromService = $userService->findOrCreate($user, 'random_provider');

        $this->assertEquals($user->id, $userFromService->id);
    }

    /** @test */
    public function can_create_user_when_logging_with_provider()
    {
        $provider = Provider::factory()->create();

        $user = User::factory()->create([
            'external_provider_id' => '1234567890'
        ]);

        $userRepositoryStub = $this->createStub(UserRepository::class);
        $userRepositoryStub->method('create')
            ->willReturn($user);

        $userService = new UserService($userRepositoryStub, app(ProviderRepository::class));
        $userFromService = $userService->findOrCreate($user, $provider->name);

        $this->assertEquals($user->id, $userFromService->id);
    }
}

