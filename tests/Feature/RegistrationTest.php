<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (!Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');

            return;
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    // public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    // {
    //     if (Features::enabled(Features::registration())) {
    //         $this->markTestSkipped('Registration support is enabled.');

    //         return;
    //     }

    //     $response = $this->get('/register');

    //     $response->assertStatus(404);
    // }

    public function test_new_users_can_register(): void
    {
        if (!Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');

            return;
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_error_message_appears_if_password_and_confirm_password_doesnt_match()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            session('errors')->first('password'),
            'The password field confirmation does not match.'
        );
    }

    public function test_error_message_appears_when_password_less_than_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            session('errors')->first('password'),
            'The password must be at least 8 characters.'
        );
    }

    public function test_email_address_must_be_unique()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertEquals(
            session('errors')->first('email'),
            'The email has already been taken.'
        );
    }

    public function test_new_user_cant_register_using_email_with_invalid_format()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid email format',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
