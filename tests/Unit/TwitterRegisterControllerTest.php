<?php

namespace Tests\Unit;

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
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class TwitterRegisterControllerTest extends TestCase
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

    public function testDestroy():void
    {
        $delete_id = '1683349434913153026';
        $response = $this->actingAs($this->user)
            ->delete('/withdraw-user/' .$delete_id);
        $response->assertOk();
        $result = FollowKeyword::where('twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = LikeKeyword::where('twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = FollowedAccount::where('user_twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = FollowTarget::where('user_twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = LikeTarget::where('user_twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = UnfollowTarget::where('user_twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = TargetBaseAccount::where('user_twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = ProtectedFollowedAccount::where('user_twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = ReservedTweet::where('twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = TwitterAccountData::where('twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
        $result = TwitterAccount::where('twitter_id', $delete_id)->exists();
        $this->assertFalse($result);
    }
}
