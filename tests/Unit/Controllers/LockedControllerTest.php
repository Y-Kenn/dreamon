<?php

namespace Tests\Unit\Controllers;

use App\Models\TwitterAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LockedControllerTest extends TestCase
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

    public function testIndex(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/locked');

        $response->assertOk()
            ->assertJsonFragment([
                'twitter_id' => $this->twitter_id,
                'locked_flag' => 0
            ]);;

    }

    public function testUpdate(): void{
        $response = $this
            ->actingAs($this->user)
            ->put('/locked/1', ['locked_flag' => true]);
        $response->assertOk();
        $locked_flag = TwitterAccount::find($this->twitter_id)
                        ->toArray()['locked_flag'];
        $this->assertEquals($locked_flag, 1);

        $response = $this
            ->actingAs($this->user)
            ->put('/locked/1', ['locked_flag' => false]);
        $response->assertOk();
        $locked_flag = TwitterAccount::find($this->twitter_id)
            ->toArray()['locked_flag'];
        $this->assertEquals($locked_flag, 0);

    }
}
