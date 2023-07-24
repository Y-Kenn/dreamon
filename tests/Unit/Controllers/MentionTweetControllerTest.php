<?php

namespace Tests\Unit\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class MentionTweetControllerTest extends TestCase
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
            ->get('/mention');
        $response->assertJsonFragment(['id' => '1683498091893358592']);
        $this->assertArrayHasKey('name', json_decode($response->content(), true)[0]);
        //複数取得できているか確認
        $this->assertGreaterThan(1, count(json_decode($response->content(), true)));
    }
}
