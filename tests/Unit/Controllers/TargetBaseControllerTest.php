<?php

namespace Tests\Unit\Controllers;

use App\Models\TargetBaseAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class TargetBaseControllerTest extends TestCase
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
        $result = TargetBaseAccount::create([
            'user_twitter_id' => $this->twitter_id,
            'base_twitter_id' => '1683349434913153026',
        ]);
        $response = $this
            ->actingAs($this->user)
            ->get('/target-base');
        $response->assertOk()
            ->assertJsonFragment([
                'id' => '1683349434913153026',
                'record_id' => $result['id']
            ]);
    }

    public function testStore(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/target-base', [
                'twitter_name' => 'taro91813',
            ]);
        $response->assertOk();
        $stored = TargetBaseAccount::where('user_twitter_id', $this->twitter_id)->latest()->first()->toArray();
        $this->assertEquals($stored['base_twitter_id'], '1683349434913153026');

    }

    public function testDestroy(): void
    {
        $result = TargetBaseAccount::create([
            'user_twitter_id' => $this->twitter_id,
            'base_twitter_id' => '1683349434913153026',
        ]);
        $response = $this->actingAs($this->user)
            ->delete('/target-base/' .$result['id']);
        $response->assertOk();
        $this->assertNull(TargetBaseAccount::find($result['id']));
    }

}
