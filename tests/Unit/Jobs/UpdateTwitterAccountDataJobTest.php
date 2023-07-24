<?php

namespace Tests\Unit\Jobs;


use App\Jobs\UpdateTwitterAccountDataJob;
use App\Models\TwitterAccountData;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UpdateTwitterAccountDataJobTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle():void
    {
        $user_twitter_id = '1683346494706028549';
        UpdateTwitterAccountDataJob::dispatch($user_twitter_id);
        $result = TwitterAccountData::latest()->first()->toArray();
        $this->assertEquals($result['twitter_id'], $user_twitter_id);
        $this->assertEquals('integer', gettype($result['following']));
        $this->assertEquals('integer', gettype($result['followers']));
    }
}
