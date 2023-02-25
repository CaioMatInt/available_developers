<?php

namespace Repository;

use App\Models\Provider;
use App\Repositories\Eloquent\ProviderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ProviderRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private ProviderRepository $providerRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->providerRepository = app(ProviderRepository::class);
    }

    /** @test */
    public function can_get_a_provider_by_its_name()
    {
        $provider = Provider::factory()->create([
            'name' => 'google',
        ]);

        $this->assertEquals($provider->id, $this->providerRepository->getIdByName('google'));
    }

    /** @test */
    public function should_get_an_exception_when_trying_to_get_a_invalid_provider()
    {
        $this->expectException(\Exception::class);
        $this->providerRepository->getIdByName('invalid-provider');
    }
}
