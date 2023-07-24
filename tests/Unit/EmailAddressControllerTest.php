<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;

class EmailAddressControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $twitter_id;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);
        $this->twitter_id = 1683346494706028549;
        Session::put('twitter_id', $this->twitter_id);
    }

    public function testIndex():void
    {
        $email = 'test@gmail.com';
        $this->user->update([
            'email' => $email
        ]);
        $response = $this
            ->actingAs($this->user)
            ->get('/email');
        $response->assertJsonFragment([
            'email' => $email
        ]);
    }

    public function testUpdate():void
    {
        $email = 'test@gmail.com';
        $response = $this
            ->actingAs($this->user)
            ->put('/email/1',
                ['email' => $email]);
        $response->assertOk();
        $this->assertEquals($this->user->email, $email);
    }
}
