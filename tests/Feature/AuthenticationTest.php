<?php

namespace Tests\Feature;

use App\Enum\ProfileTypeEnum;
use App\Models\Provider;
use App\Models\User;
use App\Services\Authentication\SocialiteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\Sanctum;
use Tests\Helper\UserTrait;
use Tests\TestCase;


class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;

    private Provider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockVariables();
    }

    private function mockUsers(): void
    {
        $this->mockAdministrator();
        $this->mockadministratorWithoutUnverifiedEmail();
    }

    private function mockVariables()
    {
        $this->mockUsers();
        $this->provider = Provider::factory()->create(['name' => Provider::GOOGLE]);
    }


    /** @test **/
    public function a_valid_and_verified_user_can_login_with_correct_email_and_password()
    {
        $userCredentials['email'] = $this->administrator->email;
        $userCredentials['password'] = $this->unhashedUserPassword;

        $response = $this->post(route('user.login'), $userCredentials);
        $response->assertOk();
        $response->assertJsonStructure([
            'access_token',
            'name'
        ]);
    }

    /** @test **/
    public function user_cant_login_without_sending_password()
    {
        $userCredentials['email'] = $this->administrator->email;

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'password' => 'The password field is required.'
        ]);
    }

    /** @test **/
    public function user_cant_login_without_sending_email()
    {
        $userCredentials['password'] = $this->unhashedUserPassword;

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'The email field is required.'
        ]);
    }

    /** @test **/
    public function user_cant_login_with_unverified_email()
    {
        Session::start();

        $userCredentials['email'] = $this->administratorWithoutUnverifiedEmail->email;
        $userCredentials['password'] = $this->unhashedUserPassword;

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'Your email address is not verified. Please, check your inbox.'
        ]);
    }

    /** @test **/
    public function user_cant_login_with_invalid_password()
    {
        $userCredentials['email'] = $this->administrator->email;
        $userCredentials['password'] = 'invalidPassword';

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.'
        ]);
    }

    /** @test **/
    public function user_cant_login_with_invalid_email()
    {
        Session::start();

        $userCredentials['email'] = 'email@mail.com';
        $userCredentials['password'] = '123456';

        $response = $this->post(route('user.login'), $userCredentials);

        $response->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.'
        ]);
    }

    /** @test **/
    public function can_register_user_with_valid_data()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'testing@mail.com';
        $userData['password'] = '12345678';
        $userData['password_confirmation'] = '12345678';
        $userData['profile_type'] = ProfileTypeEnum::Developer->name;

        $this->post(route('user.register'), $userData)->assertCreated();
    }

    /** @test **/
    public function cant_register_user_without_email()
    {
        $userData['name'] = 'Test User';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'email' => 'The email field is required.'
        ]);
    }

    /** @test **/
    public function cant_register_user_without_name()
    {
        $userData['email'] = 'email@mail.com';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'name' => 'The name field is required.'
        ]);
    }

    /** @test **/
    public function cant_register_user_without_password()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'email@mail.com';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'password' => 'The password field is required.'
        ]);
    }

    /** @test **/
    public function cant_register_user_with_an_email_that_is_already_in_use()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = $this->administrator->email;
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'email' => 'The email has already been taken.'
        ]);
    }

    /** @test **/
    public function cant_register_user_with_an_invalid_email()
    {
        $userData['name'] = 'Test User';
        $userData['email'] = 'email';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'email' => 'The email must be a valid email address.'
        ]);
    }

    /** @test **/
    public function cant_register_user_with_a_name_longer_than_255_characters()
    {
        $userData['name'] = str_repeat('a', 256);
        $userData['email'] = 'email@mail.com';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'name' => 'The name must not be greater than 255 characters.'
        ]);
    }

    /** @test **/
    public function cant_register_user_with_an_integer_name()
    {
        $userData['name'] = 123;
        $userData['email'] = 'email';
        $userData['password'] = '123456';

        $response = $this->post(route('user.register'), $userData);

        $response->assertSessionHasErrors([
            'name' => 'The name must be a string.'
        ]);
    }

    /** @test **/
    public function cant_register_with_a_password_with_less_than_8_characters()
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

    /** @test **/
    public function non_authenticated_user_cannot_logout() {
        $response = $this->json('POST', route('user.logout'));

        $response->assertUnauthorized();
    }

    /** @test **/
    public function authenticated_user_can_logout()
    {
        Sanctum::actingAs(
            $this->administrator
        );

        $response = $this->json('POST', route('user.logout'));
        $response->assertOk();
    }

    /** @test **/
    public function can_show_current_authenticated_user()
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

    /** @test **/
    public function cant_show_current_authenticated_user_when_not_logged_in()
    {
        $this->json('get', route('user.me'))->assertUnauthorized();
    }

    /** @test **/
    public function cant_ask_for_recovering_email_when_there_is_no_user_registered_with_it()
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

    /** @test **/
    public function can_ask_for_email_recovering_when_sending_an_email_that_there_is_a_user_registered_with_it()
    {
        $user = $this->administrator;
        $response = $this->json('POST', route('user.password.forgot'), [
            'email' => $user->email
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message']);
    }

    /** @test **/
    public function can_reset_password_when_token_is_valid()
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

    /** @test **/
    public function cant_reset_password_when_token_is_invalid()
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

    /** @test **/
    public function cant_reset_password_when_email_is_not_valid()
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

    /** @test **/
    public function cant_reset_password_when_password_has_less_than_8_characters()
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

    /** @test **/
    public function cant_reset_password_when_confirming_a_wrong_password()
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

    /** @test **/
    public function cant_reset_password_when_not_confirming_password()
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

    /** @test **/
    public function cant_reset_password_when_not_sending_token()
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

    /** @test **/
    public function cant_reset_password_with_expired_token()
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

    /** @test **/
    public function cant_register_user_with_administrator_profile_type()
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

    /** @test **/
    public function cant_redirect_to_login_with_provider_using_an_invalid_provider_name()
    {
        $response = $this->get(route('user.login') . '/' . 'invalid-provider');

        $response->assertSessionHasErrors([
            'provider_name' => 'The selected provider name is invalid.'
        ]);
    }

    /** @test **/
    public function should_redirect_to_login_with_provider_using_a_valid_provider_name()
    {
        $providers = config('auth.third_party_login_providers');

        if ($providers) {
            $provider = array_key_first($providers);
            $response = $this->get(route('user.login') . '/' . $provider);
            $response->assertRedirect();
        }
    }

    /** @test **/
    public function registered_user_with_google_provider_should_be_able_to_login()
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john_doe@gmail.com',
            'provider_id' => $this->provider->id,
            'external_provider_id' => '324234235235235555527'
        ]);

        $googleResponse = file_get_contents(base_path
            ('tests/Mocks/Authentication/google_provider_authentication_response.json')
        );

        $googleResponse = json_decode($googleResponse);


        $socialiteServiceStub = $this->createStub(SocialiteService::class);
        $socialiteServiceStub->method('login')
            ->willReturn($googleResponse);
        $this->app->instance(SocialiteService::class, $socialiteServiceStub);

        $this->get(route('user.login.provider.callback', $this->provider->name));
        $this->assertTrue(Auth::check());
    }

    /** @test **/
    public function unregistered_user_that_has_used_logged_with_google_should_be_able_to_get_an_account_and_get_logged()
    {
        $googleResponse = file_get_contents(base_path
            ('tests/Mocks/Authentication/google_provider_authentication_response.json')
        );

        $googleResponse = json_decode($googleResponse);

        $socialiteServiceStub = $this->createStub(SocialiteService::class);
        $socialiteServiceStub->method('login')
            ->willReturn($googleResponse);
        $this->app->instance(SocialiteService::class, $socialiteServiceStub);

        $this->get(route('user.login.provider.callback', $this->provider->name));

        $this->assertDatabaseHas('users', [
            'provider_id' => $this->provider->id,
            'external_provider_id' => $googleResponse->id,
            'name' => $googleResponse->name,
            'email' => $googleResponse->email
        ]);
        $this->assertTrue(Auth::check());
    }

    /** @test **/
    public function cannot_login_with_invalid_provider()
    {
        $response = $this->get(route('user.login.provider.callback', 'googlew'));
        $response->assertSessionHasErrors([
            'provider_name' => 'The selected provider name is invalid.'
        ]);
    }
}

