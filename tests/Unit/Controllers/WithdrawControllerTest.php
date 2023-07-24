<?php

namespace Tests\Unit\Controllers;

use App\Models\FollowedAccount;
use App\Models\FollowKeyword;
use App\Models\FollowTarget;
use App\Models\LikeKeyword;
use App\Models\LikeTarget;
use App\Models\ProtectedFollowedAccount;
use App\Models\ReservedTweet;
use App\Models\TargetBaseAccount;
use App\Models\TwitterAccount;
use App\Models\TwitterAccountData;
use App\Models\UnfollowTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class WithdrawControllerTest extends TestCase
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

    public function testDestroy(): void
    {
        $response = $this->actingAs($this->user)
            ->delete('/withdraw-user/1');
        $response->assertOk();

        $result = FollowKeyword::where('twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = LikeKeyword::where('twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = FollowedAccount::where('user_twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = FollowTarget::where('user_twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = LikeTarget::where('user_twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = UnfollowTarget::where('user_twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = TargetBaseAccount::where('user_twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = ProtectedFollowedAccount::where('user_twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = ReservedTweet::where('twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = TwitterAccountData::where('twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = TwitterAccount::where('twitter_id', $this->twitter_id)->exists();
        $this->assertFalse($result);
        $result = User::where('id', 1)->exists();
        $this->assertFalse($result);
    }
}
