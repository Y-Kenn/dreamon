<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;

class RegistPasswordControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $twitter_id;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);
        $this->twitter_id = 1683346494706028549;
        Session::put('twitter_id',$this->twitter_id);
    }

    public function testIndex():void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/regist-password');
        $response->assertOk();

        $update = User::find(1)->update(['password' => 'aaaaaaaaaa']);

        $response = $this
            ->actingAs($this->user)
            ->get('/regist-password');
        $response->assertOk();
    }

    public function testUpdate():void
    {
        $response = $this
            ->actingAs($this->user)
            ->put('/regist-password/1', ['password' => 'password',
                                            'password_confirmation' => 'password']);
        $response->assertOk();
        $hashed_pass1 = User::find(1)['password'];
        $this->assertNotNull($hashed_pass1);

        $response = $this
            ->actingAs($this->user)
            ->put('/regist-password/1', ['password' => 'password2',
                'password_confirmation' => 'password2',
                'current_password' => 'password']);

        $response->assertOk();
        $hashed_pass2 = User::find(1)['password'];
        $this->assertNotEquals($hashed_pass1, $hashed_pass2);
    }
}
