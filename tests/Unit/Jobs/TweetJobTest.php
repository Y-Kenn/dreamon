<?php

namespace Tests\Unit\Jobs;

use App\Jobs\TweetJob;
use App\Models\ReservedTweet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TweetJobTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle():void
    {
        $twitter_id = '1683346494706028549';
        $text = chr(mt_rand(65, 90)).chr(mt_rand(65, 90)).chr(mt_rand(65, 90)).
                chr(mt_rand(65, 90)).chr(mt_rand(65, 90)).chr(mt_rand(65, 90)).
                chr(mt_rand(65, 90)).chr(mt_rand(65, 90)).chr(mt_rand(65, 90));
        $record = ReservedTweet::create([
            'twitter_id' => $twitter_id,
            'text' => $text,
            'reserved_date' => date("Y/m/d H:i:s"),
        ]);
        TweetJob::dispatch($record['id'], $twitter_id, $text);
        $result = ReservedTweet::find($record['id'])->toArray();
        $this->assertNotNull($result['tweeted_at']);
        $this->assertNotNull($result['tweet_id']);
    }
}
