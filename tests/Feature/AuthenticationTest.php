<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // public function test_login_screen_can_be_rendered(): void
    // {
    //     $response = $this->get('/login');

    //     $response->assertStatus(200);
    // }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_cant_authenticate_using_email_with_invalid_format()
    {
        $response = $this->post('/login', [
            'email' => 'invalid email format',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'invalid',
        ]);

        $this->assertGuest();
    }

    public function test_error_message_appears_when_wrong_password_entered(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct_password')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertEquals(
            session('errors')->first('email'),
            'These credentials do not match our records.'
        );
    }
}
