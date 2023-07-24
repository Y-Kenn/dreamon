<?php

namespace Tests\Unit\Controllers;

use App\Models\ReservedTweet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class TweetedTweetControllerTest extends TestCase
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
            'thrown_at' => date("Y/m/d H:i:s"),
            'tweeted_at' => date("Y/m/d H:i:s"),
        ]);
        ReservedTweet::create([
            'twitter_id' => $this->twitter_id,
            'text' => $text2,
            'reserved_date' => date("Y/m/d H:i:s"),
            'thrown_at' => date("Y/m/d H:i:s"),
            'tweeted_at' => date("Y/m/d H:i:s"),
        ]);
        $response = $this
            ->actingAs($this->user)
            ->get('/tweeted-tweet');
        $response->assertOk()
            ->assertJsonFragment([
                    'text' => $text1]
            )->assertJsonFragment([
                    'text' => $text2]
            );

    }
}
