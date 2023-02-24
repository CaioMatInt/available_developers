<?php

namespace Repository;

use App\Enum\ProfileTypeEnum;
use App\Models\Recruiter;
use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;


class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = app(UserRepository::class);
    }

    /** @test */
    public function can_find_a_user_by_external_provider_id()
    {
        $user = User::factory()->create([
            'external_provider_id' => '1234567890'
        ]);

        $this->assertEquals($user->id, $this->userRepository->findByExternalProviderId($user->external_provider_id)->id);
    }

    /** @test */
    public function can_create_a_user()
    {
        $recruiter = Recruiter::factory()->create();

        $user = $this->userRepository->create([
            'name' => 'fake user',
            'photo' => null,
            'email' => 'fakeuser@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => null,
            'profile_type' => ProfileTypeEnum::Recruiter,
            'profile_id' => $recruiter->id,
            'provider_id' => null,
            'external_provider_id' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_type' => $user->profile_type,
            'profile_id' => $user->profile_id
        ]);
    }

    /** @test */
    public function can_get_authenticated_user()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertEquals($user->id, $this->userRepository->getAuthenticatedUser()->id);
    }

    /*@@TODO: implement error handling*/
}

