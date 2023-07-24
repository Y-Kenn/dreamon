<?php

namespace Tests\Unit\Controllers;

use App\Models\TwitterAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ChangeAccountControllerTest extends TestCase
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
            ->get('/change-account');
        $response->assertOk()
            ->assertJsonFragment([
                'id' => '1683346494706028549',
                'record_id' => '1683346494706028549'
            ])->assertJsonFragment([
                'id' => '1683349434913153026',
                'record_id' => '1683349434913153026'
            ]);
    }

    public function testUpdate():void
    {
        $response = $this
            ->actingAs($this->user)
            ->put('/change-account/' .'1683349434913153026',
                ['active_flag' => true]);
        $response->assertOk()
            ->assertSessionHas('twitter_id', '1683349434913153026');;
        $this->assertEquals(TwitterAccount::find('1683349434913153026')->active_flag, 1);
    }
}
