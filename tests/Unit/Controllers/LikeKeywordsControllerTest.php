<?php

namespace Tests\Unit\Controllers;

use App\Models\LikeKeyword;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LikeKeywordsControllerTest extends TestCase
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
        LikeKeyword::create([
            'twitter_id' => $this->twitter_id,
            'keywords' => $this->keywords,
            'not_flag' => false
        ]);

        $response = $this->actingAs($this->user)
            ->get('/like-keywords');

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
            ->post('/like-keywords', [
                'keywords' => $this->keywords,
                'not_flag' => false
            ]);
        $response->assertOk();

        $stored = LikeKeyword::latest()->first()->toArray();
        $this->assertEquals($stored['keywords'], $this->keywords);
        $this->assertEquals($stored['not_flag'], false);
    }

    public function testDestroy():void
    {
        $result = LikeKeyword::create([
            'twitter_id' => $this->twitter_id,
            'keywords' => $this->keywords,
            'not_flag' => false
        ]);

        $response = $this->actingAs($this->user)
            ->delete('/like-keywords/' .$result['id']);

        $response->assertOk();
        $this->assertNull(LikeKeyword::find($result['id']));
    }

}
