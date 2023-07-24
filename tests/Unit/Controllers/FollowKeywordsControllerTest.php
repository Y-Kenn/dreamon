<?php

namespace Tests\Unit\Controllers;

use App\Models\FollowKeyword;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class FollowKeywordsControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $keywords;
    private $twitter_id;

    public function setUp(): void
    {
        parent::setUp();
        $this->keywords = 'test 5';
        $this->user = User::find(1);
        $this->twitter_id = '1683346494706028549';
        Session::put('twitter_id',$this->twitter_id);
    }

    public function testIndex(): void
    {
        FollowKeyword::create([
            'twitter_id' => $this->twitter_id,
            'keywords' => $this->keywords,
            'not_flag' => false
        ]);

        $response = $this->actingAs($this->user)
            ->get('/follow-keywords');

        $response->assertOk()
            ->assertJsonFragment([
                'keywords' => $this->keywords,
                'not_flag' => 0
            ]);;

    }

    public function testStore(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post('/follow-keywords', [
                'keywords' => $this->keywords,
                'not_flag' => false
        ]);
        $response->assertOk();

        $stored = FollowKeyword::latest()->first()->toArray();
        $this->assertEquals($stored['keywords'], $this->keywords);
        $this->assertEquals($stored['not_flag'], false);
    }

    public function testDestroy():void
    {
        $result = FollowKeyword::create([
            'twitter_id' => $this->twitter_id,
            'keywords' => $this->keywords,
            'not_flag' => false
        ]);

        $response = $this->actingAs($this->user)
            ->delete('/follow-keywords/' .$result['id']);

        $response->assertOk();
        $this->assertNull(FollowKeyword::find($result['id']));
    }




}
