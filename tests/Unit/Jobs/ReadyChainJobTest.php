<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ReadyChainJob;
use App\Models\TwitterAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReadyChainJobTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle():void
    {
        $user_twitter_id = '1683346494706028549';
        TwitterAccount::find($user_twitter_id)->update(['waiting_chain_flag' => true]);
        $before = TwitterAccount::find($user_twitter_id)->toArray();
        ReadyChainJob::dispatch($user_twitter_id);
        $after = TwitterAccount::find($user_twitter_id)->toArray();
        $this->assertNotEquals($before['last_chain_at'], $after['last_chain_at']);
        $this->assertNotEquals($before['waiting_chain_flag'], $after['waiting_chain_flag']);
    }
}
