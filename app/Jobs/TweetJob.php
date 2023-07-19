<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\TwitterAccount;
use App\Models\ThrowJobsHist;
use App\Models\ReservedTweet;
use App\Library\TwitterApi;

class TweetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record_id;
    protected $twitter_id;
    protected $text;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct($record_id, $twitter_id, $text)
    {
        $this->record_id = $record_id;
        $this->twitter_id = $twitter_id;
        $this->text = $text;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {


        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken($this->twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->tweet($this->text);
        //アカウント凍結を検出
        $TwitterApi->checkAccountLocked($result, $this->twitter_id);
        Log::debug('TWEET JOB : ' .print_r($result['data']['id'], true));
        if(isset($result['data'])){
            //ツイート日時と、ツイート削除時に必要なツイートIDを保存
            ReservedTweet::find($this->record_id)->update([
                'tweeted_at' => date("Y/m/d H:i:s"),
                'tweet_id' => $result['data']['id'],
            ]);
            Log::debug('TWEET JOB : SUCCESS');
        }
    }
}
