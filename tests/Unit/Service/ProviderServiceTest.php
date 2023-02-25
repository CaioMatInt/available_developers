<?php

namespace Service;

use App\Models\Provider;
use App\Models\User;
use App\Repositories\Eloquent\ProviderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Authentication\ProviderService;
use App\Services\Authentication\SocialiteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Helper\ExternalProviderTrait;
use Tests\Helper\ProviderTrait;
use Tests\Helper\UserTrait;
use Tests\TestCase;


class ProviderServiceTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;
    use ExternalProviderTrait;
    use ProviderTrait;
    private ProviderService $providerService;

    public function setUp(): void
    {
        parent::setUp();
        $this->providerService = app(ProviderService::class);
        $this->mockVariables();
    }

    private function mockVariables()
    {
        $this->mockAdministrator();
        $this->mockGoogleProvider();
        $this->mockGoogleResponse();
    }

    /** @test */
    public function can_find_user_when_logging_with_provider()
    {
        $user = User::factory()->create([
            'external_provider_id' => $this->googleResponse->id,
            'name' => $this->googleResponse->name,
            'email' => $this->googleResponse->email
        ]);

        $userRepositoryStub = $this->createStub(UserRepository::class);
        $userRepositoryStub->method('findByExternalProviderId')
            ->willReturn($user);

        $socialiteServiceStub = $this->createStub(SocialiteService::class);
        $socialiteServiceStub->method('login')
            ->willReturn($this->googleResponse);

        $providerService = new ProviderService($userRepositoryStub, $socialiteServiceStub, app(ProviderRepository::class));
        $providerService->callback(Provider::GOOGLE);

        $this->assertTrue(Auth::check());
    }

    /** @test */
    public function can_create_user_when_logging_with_provider()
    {
        $user = User::factory()->create([
            'external_provider_id' => '1234567890'
        ]);

        $userRepositoryStub = $this->createStub(UserRepository::class);
        $userRepositoryStub->method('create')
            ->willReturn($user);

        $socialiteServiceStub = $this->createStub(SocialiteService::class);
        $socialiteServiceStub->method('login')
            ->willReturn($this->googleResponse);

        $providerService = new ProviderService($userRepositoryStub, $socialiteServiceStub, app(ProviderRepository::class));
        $providerService->callback(Provider::GOOGLE);

        $this->assertTrue(Auth::check());
    }
}

