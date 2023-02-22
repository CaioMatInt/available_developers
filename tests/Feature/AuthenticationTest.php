<?php

namespace Tests\Feature;

use App\Enum\ProfileTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;


class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;
    private User $administratorWithoutUnverifiedEmail;
    private string $unhashedUserPassword = '123456';

    public function setUp(): void
    {
        parent::setUp();
        $this->mockUsers();
    }

    private function mockUsers(): void
    {
        $this->administrator = User::factory()->create([
            'password' => Hash::make($this->unhashedUserPassword)
        ]);

        $this->administratorWithoutUnverifiedEmail = User::factory()->create([
            'password' => Hash::make($this->unhashedUserPassword),
            'email_verified_at' => null
        ]);
    }


    public function test_a_valid_and_verified_user_can_login_with_correct_email_and_password()
    {
        $userCredentials['email'] = $this->administrator->email;
        $userCredentials['password'] = $this->unhashedUserPassword;

        $response = $this->post(route('user.login'), $userCredentials);
        dd($response->json());
        $response->assertOk();
        $response->assertJsonStructure([
            'access_token',
            'name'
        ]);
    }

    public function test_user_cant_login_without_sending_password()
    {
        $userCredentials['email'] = $this->administrator->email;

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'password' => 'The password field is required.'
        ]);
    }

    public function test_user_cant_login_without_sending_email()
    {
        $userCredentials['password'] = $this->unhashedUserPassword;

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'The email field is required.'
        ]);
    }

    public function test_user_cant_login_with_unverified_email()
    {
        Session::start();

        $userCredentials['email'] = $this->administratorWithoutUnverifiedEmail->email;
        $userCredentials['password'] = $this->unhashedUserPassword;

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'Your email address is not verified. Please, check your inbox.'
        ]);
    }

    public function test_user_cant_login_with_invalid_password()
    {
        $userCredentials['email'] = $this->administrator->email;
        $userCredentials['password'] = 'invalidPassword';

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.'
        ]);
    }

    public function test_user_cant_login_with_invalid_email()
    {
        Session::start();

        $userCredentials['email'] = 'email@mail.com';
        $userCredentials['password'] = '123456';

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.'
        ]);
    }

    public function test_can_register_user_with_valid_data()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'testing@mail.com';
        $userData['password'] = '12345678';
        $userData['password_confirmation'] = '12345678';
        $userData['profile_type'] = ProfileTypeEnum::Developer->name;

        $this->post(route('user.register'), $userData)->assertCreated();
    }

    public function test_cant_register_user_without_email()
    {
        $userData['name'] = 'Test User';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'email' => 'The email field is required.'
        ]);
    }

    public function test_cant_register_user_without_name()
    {
        $userData['email'] = 'email@mail.com';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'name' => 'The name field is required.'
        ]);
    }

    public function test_cant_register_user_without_password()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'email@mail.com';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'password' => 'The password field is required.'
        ]);
    }

    public function test_cant_register_user_with_an_email_that_is_already_in_use()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = $this->administrator->email;
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'email' => 'The email has already been taken.'
        ]);
    }

    public function test_cant_register_user_with_an_invalid_email()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'email';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'email' => 'The email must be a valid email address.'
        ]);
    }

    public function test_cant_register_user_with_a_name_longer_than_255_characters()
    {
        $userData['name'] = str_repeat('a', 256);
        $userData['email'] = 'email@mail.com';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'name' => 'The name must not be greater than 255 characters.'
        ]);
    }

    public function test_cant_register_user_with_an_integer_name()
    {
        $userData['name'] = 123;
        $userData['email'] = 'email';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'name' => 'The name must be a string.'
        ]);
    }

    public function test_cant_register_with_a_password_with_less_than_8_characters()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'email@mail.com';
        $userData['password'] = '1234567';
        $userData['password_confirmation'] = '1234567';
        $userData['profile_type'] = ProfileTypeEnum::Developer->name;

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'password' => 'The password must be at least 8 characters.'
        ]);
    }

    public function test_non_authenticated_user_cannot_logout() {
        $response = $this->json('POST', route('user.logout'));

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_logout()
    {
        Sanctum::actingAs(
            $this->administrator
        );

        $response = $this->json('POST', route('user.logout'));
        $response->assertOk();
    }

    public function test_can_show_current_authenticated_user()
    {
        $response = $this->actingAs($this->administrator)->get(route('user.me'));
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'name',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_cant_show_current_authenticated_user_when_not_logged_in()
    {
        $this->json('get', route('user.me'))->assertUnauthorized();
    }

    public function test_cant_ask_for_recovering_email_when_there_is_no_user_registered_with_it()
    {
        $response = $this->json('POST', route('user.password.forgot'), [
            'email' => 'invalid@email.com'
        ]);

        $response->assertUnprocessable();
        $this->assertEquals(
            'The selected email is invalid.',
            $response->json('errors.email.0')
        );
    }

    public function test_can_ask_for_email_recovering_when_sending_an_email_that_there_is_a_user_registered_with_it()
    {
        $user = $this->administrator;
        $response = $this->json('POST', route('user.password.forgot'), [
            'email' => $user->email
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message']);
    }

    public function test_can_reset_password_when_token_is_valid()
    {
        $user = $this->administrator;
        $token = Password::broker()->createToken($user);
        $newPassword = '12345678';

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'message'
        ]);

        $this->assertEquals("Your password has been reset!", $response['message']);
    }

    public function test_cant_reset_password_when_token_is_invalid()
    {
        $user = $this->administrator;
        $newPassword = '12345678';

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertStatus(422)->assertJsonStructure([
            'message'
        ]);

        $this->assertEquals("This password reset token is invalid.", $response['message']);
    }

    public function test_cant_reset_password_when_email_is_not_valid()
    {
        $newPassword = '12345678';

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => 'invalid-token',
            'email' => 'invalid@invalid.com',
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertUnprocessable();
        $this->assertEquals(
            'The selected email is invalid.',
            $response->json('errors.email.0')
        );
    }

    public function test_cant_reset_password_when_password_has_less_than_8_characters()
    {
        $user = $this->administrator;
        $token = Password::broker()->createToken($user);
        $newPassword = '1234567';

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertUnprocessable();
        $this->assertEquals(
            'The password must be at least 8 characters.',
            $response->json('errors.password.0')
        );
    }

    public function test_cant_reset_password_when_confirming_a_wrong_password()
    {
        $user = $this->administrator;
        $token = Password::broker()->createToken($user);
        $newPassword = '12345678';

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => 'invalid-password'
        ]);

        $response->assertUnprocessable();

        $this->assertEquals(
            'The password confirmation does not match.',
            $response->json('errors.password.0')
        );
    }

    public function test_cant_reset_password_when_not_confirming_password()
    {
        $user = $this->administrator;
        $token = Password::broker()->createToken($user);
        $newPassword = '12345678';

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword
        ]);

        $response->assertUnprocessable();

        $this->assertEquals(
            'The password confirmation field is required.',
            $response->json('errors.password_confirmation.0')
        );
    }

    public function test_cant_reset_password_when_not_sending_token()
    {
        $user = $this->administrator;
        $newPassword = '12345678';

        $response = $this->json('POST', route('user.password.reset'), [
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertUnprocessable();

        $this->assertEquals(
            'The token field is required.',
            $response->json('errors.token.0')
        );
    }

    public function test_cant_reset_password_with_expired_token()
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);
        $password = 'newpassword';

        DB::table('password_resets')->where('email', $user->email)->update([
            'created_at' => now()->subHours(2)
        ]);

        $response = $this->json('POST', route('user.password.reset'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message'
        ]);
        $this->assertEquals("This password reset token is invalid.", $response->json('message'));
    }

    public function test_can_register_user_with_administrator_profile_type()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'testing@mail.com';
        $userData['password'] = '12345678';
        $userData['password_confirmation'] = '12345678';
        $userData['profile_type'] = ProfileTypeEnum::Administrator->name;

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'profile_type' => 'The selected profile type is invalid.'
        ]);
    }
}

