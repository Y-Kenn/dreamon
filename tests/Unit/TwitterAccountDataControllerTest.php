<?php

namespace Tests\Unit;

use App\Models\FollowedAccount;
use App\Models\LikeTarget;
use App\Models\TwitterAccountData;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;

class TwitterAccountDataControllerTest extends TestCase
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
        FollowedAccount::create([
            'user_twitter_id' => $this->twitter_id,
            'target_twitter_id' => '1683349434913153026',
            'followed_at' => date("Y/m/d H:i:s"),
            'unfollowed_at' => date("Y/m/d H:i:s"),
        ]);
        LikeTarget::create([
            'user_twitter_id' => $this->twitter_id,
            'target_tweet_id' => '1683349434913153000',
            'thrown_at' => date("Y/m/d H:i:s"),
            'liked_at' => date("Y/m/d H:i:s"),
        ]);
        TwitterAccountData::create([
            'twitter_id' => $this->twitter_id,
            'following' => 0,
            'followers' => 0,
        ]);
        $response = $this
            ->actingAs($this->user)
            ->get('/twitter-data');
        $content = json_decode($response->content(), true);
        $this->assertEquals('integer', gettype($content['following_today']));
        $this->assertEquals('integer', gettype($content['following_30days']));
        $this->assertEquals('integer', gettype($content['followers_today']));
        $this->assertEquals('integer', gettype($content['followers_30days']));
        $this->assertEquals('integer', gettype($content['unfollowing_today']));
        $this->assertEquals('integer', gettype($content['unfollowing_30days']));
        $this->assertEquals('integer', gettype($content['like_today']));
        $this->assertEquals('integer', gettype($content['like_30days']));
    }
}
