<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        User::find(1)->update([
            'email' => 'test@gmail.com' ,
            'password' => Hash::make('password')
        ]);
        $response = $this->post('/login', [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $this->post('/login', [
            'email' => 'test@gmail.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
