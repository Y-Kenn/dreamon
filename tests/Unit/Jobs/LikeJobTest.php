<?php

namespace Tests\Unit\Jobs;

use App\Jobs\LikeJob;
use App\Models\LikeTarget;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LikeJobTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle():void
    {
        $user_twitter_id = '1683346494706028549';
        $target_tweet_id = '1683498091893358592';
        $target = LikeTarget::create([
            'user_twitter_id' => $user_twitter_id,
            'target_tweet_id' => $target_tweet_id,
        ]);
        LikeJob::dispatch($target['id'], $user_twitter_id, $target_tweet_id);
        $result = LikeTarget::find($target['id'])->toArray();
        $this->assertNotNull($result['liked_at']);

    }
}
