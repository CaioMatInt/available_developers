<?php

namespace Tests\Helper;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait UserTrait
{
    private User $administrator;
    private User $administratorWithoutUnverifiedEmail;
    private string $unhashedUserPassword = '123456';

    private function mockAdministrator(): void
    {
        $this->administrator = User::factory()->create([
            'password' => Hash::make($this->unhashedUserPassword)
        ]);
    }

    private function mockAdministratorWithoutUnverifiedEmail(): void
    {
        $this->administratorWithoutUnverifiedEmail = User::factory()->create([
            'password' => Hash::make($this->unhashedUserPassword),
            'email_verified_at' => null
        ]);
    }
}
