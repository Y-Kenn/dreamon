<?php

namespace Tests\Unit\Controllers;

use App\Models\ReservedTweet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ReservedTweetControllerTest extends TestCase
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
        $text1 = 'test1';
        $text2 = 'test2';
        ReservedTweet::create([
            'twitter_id' => $this->twitter_id,
            'text' => $text1,
            'reserved_date' => date("Y/m/d H:i:s"),
        ]);
        ReservedTweet::create([
            'twitter_id' => $this->twitter_id,
            'text' => $text2,
            'reserved_date' => date("Y/m/d H:i:s"),
        ]);
        $response = $this
            ->actingAs($this->user)
            ->get('/reserved-tweet');
        $response->assertOk()
            ->assertJsonFragment([
                'text' => $text1]
            )->assertJsonFragment([
                    'text' => $text2]
            );

    }

    public function testStore(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/reserved-tweet', [
                'text' => 'test',
                'reserved_date' => date("Y/m/d H:i:s"),
            ]);
        $response->assertOk();
        $stored = ReservedTweet::latest()->first()->toArray();
        $this->assertEquals($stored['twitter_id'], $this->twitter_id);
        $this->assertEquals($stored['text'], 'test');
    }

    public function testDestroy():void
    {
        $result = ReservedTweet::create([
            'twitter_id' => $this->twitter_id,
            'text' => 'test',
            'reserved_date' => date("Y/m/d H:i:s"),
        ]);
        $response = $this->actingAs($this->user)
            ->delete('/reserved-tweet/' .$result['id']);
        $response->assertOk();
        $this->assertNull(ReservedTweet::find($result['id']));

    }
}
