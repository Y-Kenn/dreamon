<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use App\Models\TwitterAccountData;

class UpdateTwitterAccountDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_twitter_id;

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
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                        env('API_SECRET'), 
                                        env('BEARER'), 
                                        env('CLIENT_ID'), 
                                        env('CLIENT_SECRET'), 
                                        env('REDIRECT_URI'));
        
        $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->getUserInfoByIds([$this->user_twitter_id]);
        if(isset($result['data'])){
            TwitterAccountData::create([
                'twitter_id' => $this->user_twitter_id,
                'following' => $result['data'][0]['public_metrics']['following_count'],
                'followers' => $result['data'][0]['public_metrics']['followers_count'],
            ]);
        }else{
            Log::debug('FAILED UPDATE TWITTER ACCOUNT DATA : ' . print_r($result, true));
        }


    }
}
