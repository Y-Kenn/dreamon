<?php

namespace App\Jobs;

use App\Library\DBErrorHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ReservedTweet;
use App\Library\TwitterApi;
use mysql_xdevapi\Exception;

class TweetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record_id;
    protected $twitter_id;
    protected $text;

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
        if(isset($result['data'])){
            //ツイート日時と、ツイート削除時に必要なツイートIDを保存
            try{
                DB::transaction(function () use($result){
                    $db_result = ReservedTweet::find($this->record_id)->update([
                        'tweeted_at' => date("Y/m/d H:i:s"),
                        'tweet_id' => $result['data']['id'],
                    ]);
                    DBErrorHandler::checkUpdated($db_result);
                    Log::debug('TWEET JOB : SUCCESS');
                });
            } catch (\Throwable $e) {
                Log::error('[ERROR] TWEET JOB : ' . print_r($e->getMessage(), true));
            }
        }

    }
}
