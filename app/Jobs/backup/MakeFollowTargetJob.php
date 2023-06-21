<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeFollowTargetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct($user_twitter_id)
    {
        $this->user_twitter_id = $user_twitter_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $twitter_account_info = TwitterAccount::find($this->user_twitter_id);
        $access_token = $twitter_account_info->access_token;

        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));
        $TwitterApi->setTokenToHeader($access_token);

    }
}
